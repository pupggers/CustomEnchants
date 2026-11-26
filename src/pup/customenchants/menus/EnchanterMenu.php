<?php

declare(strict_types=1);

namespace pup\customenchants\menus;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\XpCollectSound;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\items\types\BlackScroll;
use pup\customenchants\items\types\EnchantBook;
use pup\customenchants\items\types\WhiteScroll;
use pup\customenchants\Main;
use pup\customenchants\MessageService;
use pup\customenchants\utils\Rarity;

final class EnchanterMenu
{
	private const array BOOK_SLOTS = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25];
	private const array ITEM_SLOTS = [
		'white_scroll' => 38,
		'holy_scroll' => 40,
		'black_scroll' => 42,
	];
	private const int TINKERER_SLOT = 49;

	private const array DEFAULT_BOOK_PRICES = [
		'COMMON' => 'level:5',
		'UNCOMMON' => 'level:10',
		'RARE' => 'level:15',
		'EPIC' => 'level:25',
		'LEGENDARY' => 'level:35',
		'MASTERY' => false,
		'HEROIC' => false,
		'default' => 'level:50',
	];
	private const array DEFAULT_ITEM_PRICES = [
		'white_scroll' => 'level:30',
		'holy_scroll' => 'level:60',
		'black_scroll' => 'level:20',
	];

	public static function open(Player $player): void
	{
		$offers = self::offers();

		$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
		$menu->setName(Main::getInstance()->getMessages()->format((string) self::config('title', '{dark_purple}Enchanter')));

		$inventory = $menu->getInventory();
		foreach ($offers as $slot => [$item, $price]) {
			$display = clone $item;
			$display->setLore([
				...$display->getLore(),
				'',
				TextFormat::RESET . MenuItems::text('shop_price_lore', '{gray}Price: {yellow}{price}', ['price' => $price->format()]),
				TextFormat::RESET . MenuItems::text('shop_buy_lore', '{green}Click to buy'),
			]);
			$inventory->setItem($slot, $display);
		}
		$inventory->setItem(self::TINKERER_SLOT, MenuItems::button(
			VanillaBlocks::ANVIL()->asItem(),
			MenuItems::text('tinkerer_button_name', '{gold}Tinkerer'),
			[MenuItems::text('tinkerer_button_lore', '{gray}Trade enchantment books for Magic Dust')]
		));
		MenuItems::fill($inventory);

		$menu->setListener(InvMenu::readonly(static function (DeterministicInvMenuTransaction $transaction) use ($offers): void {
			$slot = $transaction->getAction()->getSlot();
			if ($slot === self::TINKERER_SLOT) {
				$transaction->then(static fn(Player $player) => TinkererMenu::open($player));
				return;
			}
			$offer = $offers[$slot] ?? null;
			if ($offer !== null) {
				$transaction->then(static fn(Player $player) => self::buy($player, $offer[0], $offer[1]));
			}
		}));

		$menu->send($player);
	}

	private static function buy(Player $player, Item $item, Price $price): void
	{
		$bought = clone $item;
		if (!$player->getInventory()->canAddItem($bought)) {
			MessageService::send($player, 'shop_inventory_full');
			return;
		}
		if (!$price->take($player)) {
			MessageService::send($player, 'shop_cannot_afford', ['price' => $price->format()]);
			return;
		}
		$player->getInventory()->addItem($bought);
		$player->getWorld()->addSound($player->getPosition(), new XpCollectSound(), [$player]);
		MessageService::send($player, 'shop_bought', ['item' => $bought->getCustomName(), 'price' => $price->format()]);
	}

	/** @return array<int, array{0: Item, 1: Price}> */
	private static function offers(): array
	{
		$offers = [];

		if (ItemRegistry::isEnabled('random_enchant_book')) {
			$prices = self::config('books', []);
			$prices = (is_array($prices) ? $prices : []) + self::DEFAULT_BOOK_PRICES;
			$slots = self::BOOK_SLOTS;
			$rarities = Rarity::enabled();
			usort($rarities, static fn(Rarity $a, Rarity $b): int => $a->value <=> $b->value);
			foreach ($rarities as $rarity) {
				$price = Price::parse(array_key_exists($rarity->name, $prices) ? $prices[$rarity->name] : $prices['default']);
				if ($price === null || !self::hasEnchants($rarity) || ($slot = array_shift($slots)) === null) {
					continue;
				}
				$offers[$slot] = [new EnchantBook($rarity->value)->getItem(), $price];
			}
		}

		$prices = self::config('items', self::DEFAULT_ITEM_PRICES);
		$prices = is_array($prices) ? $prices : [];
		$items = [
			'white_scroll' => static fn(): Item => new WhiteScroll()->getItem(),
			'holy_scroll' => static fn(): Item => new WhiteScroll(true)->getItem(),
			'black_scroll' => static fn(): Item => new BlackScroll(ItemRegistry::getChance('black_scroll', 50))->getItem(),
		];
		foreach ($items as $id => $make) {
			$price = Price::parse($prices[$id] ?? null);
			if ($price === null || !ItemRegistry::isEnabled($id)) {
				continue;
			}
			$offers[self::ITEM_SLOTS[$id]] = [$make(), $price];
		}

		return $offers;
	}

	private static function hasEnchants(Rarity $rarity): bool
	{
		foreach (Main::getInstance()->getEnchantManager()->getEnabled() as $enchant) {
			if ($enchant->getRarity() === $rarity->value) {
				return true;
			}
		}
		return false;
	}

	private static function config(string $key, mixed $default): mixed
	{
		return Main::getInstance()->getConfig()->getNested('shop.' . $key, $default);
	}
}
