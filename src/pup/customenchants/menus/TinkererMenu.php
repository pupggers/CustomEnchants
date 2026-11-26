<?php

declare(strict_types=1);

namespace pup\customenchants\menus;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\items\types\EnchantmentBook;
use pup\customenchants\items\types\MagicDust;
use pup\customenchants\Main;
use pup\customenchants\MessageService;
use pup\customenchants\utils\Rarity;

final class TinkererMenu
{
	private const int INPUT_SLOTS = 45;
	private const int BACK_SLOT = 45;
	private const int CONFIRM_SLOT = 49;

	private const array DEFAULT_DUST = [
		'COMMON' => 1,
		'UNCOMMON' => 1,
		'RARE' => 2,
		'EPIC' => 2,
		'LEGENDARY' => 3,
		'MASTERY' => 4,
		'HEROIC' => 5,
	];

	public static function open(Player $player): void
	{
		$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
		$menu->setName(Main::getInstance()->getMessages()->format((string) self::config('title', '{gold}Tinkerer')));

		$inventory = $menu->getInventory();
		$inventory->setItem(self::BACK_SLOT, MenuItems::button(VanillaItems::ARROW(), MenuItems::text('tinkerer_back_name', '{yellow}Back to the Enchanter')));
		self::refresh($inventory);
		for ($slot = self::INPUT_SLOTS; $slot < $inventory->getSize(); $slot++) {
			if ($inventory->getItem($slot)->isNull()) {
				$inventory->setItem($slot, MenuItems::filler());
			}
		}

		$menu->setListener(static function (InvMenuTransaction $transaction) use ($inventory): InvMenuTransactionResult {
			$slot = $transaction->getAction()->getSlot();
			if ($slot >= self::INPUT_SLOTS) {
				$result = $transaction->discard();
				return match ($slot) {
					self::CONFIRM_SLOT => $result->then(static fn(Player $player) => self::trade($player, $inventory)),
					self::BACK_SLOT => $result->then(static fn(Player $player) => EnchanterMenu::open($player)),
					default => $result,
				};
			}
			$in = $transaction->getIn();
			if (!$in->isNull() && self::dustFor($in) === null) {
				return $transaction->discard();
			}
			return $transaction->continue()->then(static fn() => self::refresh($inventory));
		});
		$menu->setInventoryCloseListener(static function (Player $player, Inventory $inventory): void {
			self::giveBack($player, $inventory);
		});

		$menu->send($player);
	}

	public static function dustFor(Item $item): ?int
	{
		$tag = $item->getNamedTag();
		$rarity = match (true) {
			EnchantmentBook::isBook($item) => Main::getInstance()->getEnchantManager()->getByKey($tag->getString('enchant', ''))?->getRarity(),
			$tag->getString('customItem', '') === 'random_enchant_book' => $tag->getInt('rarity', Rarity::COMMON),
			default => null,
		};
		if ($rarity === null) {
			return null;
		}
		$amounts = self::config('dust', self::DEFAULT_DUST);
		$name = Rarity::fromValue($rarity)->name;
		$amount = (int) (is_array($amounts) && isset($amounts[$name]) ? $amounts[$name] : self::config('default_dust', 1));
		return $amount > 0 ? $amount : null;
	}

	private static function trade(Player $player, Inventory $inventory): void
	{
		$dust = ItemRegistry::getAll()[MagicDust::ID] ?? null;
		if ($dust === null) {
			MessageService::send($player, 'tinkerer_disabled');
			return;
		}

		[$books, $total] = self::count($inventory);
		if ($books === 0) {
			MessageService::send($player, 'tinkerer_nothing');
			return;
		}

		for ($slot = 0; $slot < self::INPUT_SLOTS; $slot++) {
			if (self::dustFor($inventory->getItem($slot)) !== null) {
				$inventory->clear($slot);
			}
		}
		self::refresh($inventory);

		$rewards = [];
		$max = $dust->getItem()->getMaxStackSize();
		for ($left = $total; $left > 0; $left -= $max) {
			$rewards[] = $dust->getItem()->setCount(min($max, $left));
		}
		foreach ($player->getInventory()->addItem(...$rewards) as $rest) {
			$player->getWorld()->dropItem($player->getPosition(), $rest);
		}
		$player->getWorld()->addSound($player->getPosition(), new AnvilUseSound(), [$player]);
		MessageService::send($player, 'tinkerer_traded', ['books' => $books, 'dust' => $total]);
	}

	private static function refresh(Inventory $inventory): void
	{
		[$books, $total] = self::count($inventory);
		$ready = $books > 0;
		$inventory->setItem(self::CONFIRM_SLOT, MenuItems::button(
			VanillaBlocks::STAINED_GLASS_PANE()->setColor($ready ? DyeColor::LIME : DyeColor::RED)->asItem(),
			MenuItems::text('tinkerer_confirm_name', '{green}Trade for Magic Dust'),
			[MenuItems::text('tinkerer_confirm_lore', '{gray}{books} book(s) -> {light_purple}{dust} Magic Dust', ['books' => $books, 'dust' => $total])]
		));
	}

	/** @return array{0: int, 1: int} */
	private static function count(Inventory $inventory): array
	{
		$books = 0;
		$total = 0;
		for ($slot = 0; $slot < self::INPUT_SLOTS; $slot++) {
			$item = $inventory->getItem($slot);
			$dust = self::dustFor($item);
			if ($dust !== null) {
				$books += $item->getCount();
				$total += $dust * $item->getCount();
			}
		}
		return [$books, $total];
	}

	private static function giveBack(Player $player, Inventory $inventory): void
	{
		$items = [];
		for ($slot = 0; $slot < self::INPUT_SLOTS; $slot++) {
			$item = $inventory->getItem($slot);
			if (!$item->isNull()) {
				$items[] = $item;
				$inventory->clear($slot);
			}
		}
		if ($items === []) {
			return;
		}
		foreach ($player->getInventory()->addItem(...$items) as $rest) {
			$player->getWorld()->dropItem($player->getPosition(), $rest);
		}
	}

	private static function config(string $key, mixed $default): mixed
	{
		return Main::getInstance()->getConfig()->getNested('tinkerer.' . $key, $default);
	}
}
