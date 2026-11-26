<?php

namespace pup\customenchants\items\types;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\Main;
use pup\customenchants\types\DurabilityEnchant;

final class WhiteScroll extends CustomItem implements InventoryTransactionInterface
{
	public function __construct(private readonly bool $holy = false)
	{
		$item = VanillaItems::PAPER();
		$item->getNamedTag()
			->setString("holy_scroll", ($this->holy ? "holy" : "normal"))
			->setString(self::CUSTOM_ITEM_ID, "white_scroll");
		$definition = $this->holy ? 'holy_scroll' : 'white_scroll';
		parent::__construct('white_scroll', ItemRegistry::getName($definition, 'White Scroll'), ItemRegistry::getLore($definition, ['Protects an item from being destroyed by enchant books']), $item);
	}

	public function onInventoryTransaction(
		InventoryTransactionEvent $e,
		Player                    $player,
		Item                      $item,
		Item                      $target,
		SlotChangeAction          $action1,
		SlotChangeAction          $action2
	)
	: void
	{
		$scroll = null;
		$targetItem = null;
		$scrollAction = null;
		$targetAction = null;

		if ($this->isScroll($item)) {
			$scroll = $item;
			$targetItem = $target;
			$scrollAction = $action1;
			$targetAction = $action2;
		} elseif ($this->isScroll($target)) {
			$scroll = $target;
			$targetItem = $item;
			$scrollAction = $action2;
			$targetAction = $action1;
		}

		if ($scroll === null || $targetItem === null || $targetItem->isNull()) {
			return;
		}

		if ($targetItem->getNamedTag()->getTag(self::CUSTOM_ITEM_ID) !== null) {
			return;
		}

		if (!$targetItem instanceof Durable) {
			Main::getInstance()->getMessages()->send($player, 'white_scroll_durable_only');
			$e->cancel();
			return;
		}

		if ($this->isProtected($targetItem)) {
			Main::getInstance()->getMessages()->send($player, 'white_scroll_protected');
			$e->cancel();
			return;
		}

		$lore = $targetItem->getLore();
		if ($this->isHoly($scroll)) {
			$targetItem->getNamedTag()->setString("holy_scroll", "holy");
			$lore[] = Main::getInstance()->getMessages()->format('{gold}{bold}HOLY PROTECTED');
		} else {
			$targetItem->getNamedTag()->setString("white_scroll", "protected");
			$lore[] = Main::getInstance()->getMessages()->format('{white}{bold}PROTECTED');
		}

		$targetItem->setLore($lore);

		$scroll->pop();
		$scrollAction->getInventory()->setItem($scrollAction->getSlot(), $scroll->getCount() > 0 ? $scroll : VanillaItems::AIR());

		$targetAction->getInventory()->setItem($targetAction->getSlot(), $targetItem);

		$player->getWorld()->addSound($player->getLocation(), new AnvilUseSound());
		$e->cancel();
	}

	private function isScroll(Item $item)
	: bool
	{
		return $item->getNamedTag()->getString(self::CUSTOM_ITEM_ID, "") === "white_scroll";
	}

	private function isProtected(Item $item)
	: bool
	{
		return self::isProtectedItem($item);
	}

	public static function isProtectedItem(Item $item)
	: bool
	{
		return $item->getNamedTag()->getString("white_scroll", "") === "protected" ||
			$item->getNamedTag()->getString("holy_scroll", "") === "holy";
	}

	public static function removeProtection(Item $item)
	: void
	{
		$messages = Main::getInstance()->getMessages();
		$protectedLines = [
			$messages->format('{white}{bold}PROTECTED'),
			$messages->format('{gold}{bold}HOLY PROTECTED')
		];
		$item->setLore(array_values(array_filter(
			$item->getLore(),
			static fn(string $line): bool => !in_array($line, $protectedLines, true)
		)));
		$item->getNamedTag()->removeTag("white_scroll", "holy_scroll");

		self::syncUnbreakable($item);
	}

	public static function syncUnbreakable(Item $item)
	: void
	{
		if (!$item instanceof Durable) {
			return;
		}
		foreach ($item->getEnchantments() as $instance) {
			$enchant = $instance->getType();
			if ($enchant instanceof DurabilityEnchant && $enchant->makesUnbreakable()) {
				$item->setUnbreakable();
				return;
			}
		}
		$item->setUnbreakable(false);
	}

	private function isHoly(Item $item)
	: bool
	{
		return $item->getNamedTag()->getString("holy_scroll", "normal") === "holy";
	}

	public function setHoly(): Item
	{
		return new self(true)->getItem();
	}

	public static function isHolyItem(Item $item) : bool
	{
		return $item->getNamedTag()->getString("holy_scroll", "") === "holy";
	}

	public function removeHoly(): Item
	{
		return $this->isHoly($this->getItem()) ? new self()->getItem() : $this->getItem();
	}
}