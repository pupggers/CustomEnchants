<?php

namespace pup\customenchants\items\types;

use InvalidArgumentException;
use pocketmine\block\utils\DyeColor;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pup\customenchants\Main;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use Random\RandomException;

final class BlackScroll extends CustomItem implements InventoryTransactionInterface
{
    private const int MIN_CHANCE = 1;
    private const int MAX_CHANCE = 100;

    public function __construct(private int $chance = 50)
    {
        if ($chance < self::MIN_CHANCE || $chance > self::MAX_CHANCE) {
            throw new InvalidArgumentException(
                "Chance must be between " . self::MIN_CHANCE . " and " . self::MAX_CHANCE
            );
        }

        $item = VanillaItems::DYE()->setColor(DyeColor::BLACK);
        $item->getNamedTag()
            ->setInt("chance", $chance)
            ->setString(self::CUSTOM_ITEM_ID, "black_scroll");

        parent::__construct('black_scroll', ItemRegistry::getName('black_scroll', 'Black Scroll', ['chance' => $chance]), ItemRegistry::getLore('black_scroll', ['Removes a random enchantment'], ['chance' => $chance]), $item);
    }

    /**
     * @throws RandomException
     */
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
        $transaction = $e->getTransaction();

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

        if (!$targetItem instanceof Durable) {
            Main::getInstance()->getMessages()->send($player, 'black_scroll_durable_only');
            $e->cancel();
            return;
        }

        $enchantments = $targetItem->getEnchantments();
        if (count($enchantments) === 0) {
            Main::getInstance()->getMessages()->send($player, 'black_scroll_no_enchants');
            $e->cancel();
            return;
        }

        $randomIndex = array_rand($enchantments);
        $enchantmentToRemove = $enchantments[$randomIndex];
        $chance = $scroll->getNamedTag()->getInt("chance", $this->chance);

        if (random_int(1, 100) <= $chance) {
            $targetItem->removeEnchantment($enchantmentToRemove->getType());
            $targetItem = Main::getInstance()->getEnchantManager()->rebuildLore($targetItem);
            Main::getInstance()->getMessages()->send($player, 'black_scroll_success');
        } else {
            Main::getInstance()->getMessages()->send($player, 'black_scroll_failure');
        }

        $scroll->pop();
        $scrollAction->getInventory()->setItem($scrollAction->getSlot(), $scroll->getCount() > 0 ? $scroll : VanillaItems::AIR());

        $targetAction->getInventory()->setItem($targetAction->getSlot(), $targetItem);

        $e->cancel();
    }

    private function isScroll(Item $item)
    : bool
    {
        return $item->getNamedTag()->getString("customItem", "") === "black_scroll";
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function setChance(int $chance): void
    {
        $this->chance = $chance;

    }
}
