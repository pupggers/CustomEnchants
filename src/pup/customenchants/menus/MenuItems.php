<?php

declare(strict_types=1);

namespace pup\customenchants\menus;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pup\customenchants\Main;
use pup\placeholderapi\PlaceholderApi;

final class MenuItems
{
	public static function filler(): Item
	{
		return VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY)->asItem()->setCustomName(PlaceholderApi::parse("{reset}" . " "));

	}

	/** @param list<string> $lore */
	public static function button(Item $item, string $name, array $lore = []): Item
	{
		$messages = Main::getInstance()->getMessages();
		return $item
			->setCustomName(PlaceholderApi::parse(TextFormat::RESET . $messages->format($name)))
			->setLore(array_map(static fn(string $line): string => PlaceholderApi::parse(TextFormat::RESET . $messages->format($line)), $lore));
	}

	/** @param list<int> $except */
	public static function fill(Inventory $inventory, array $except = []): void
	{
		$filler = self::filler();
		for ($slot = 0, $size = $inventory->getSize(); $slot < $size; $slot++) {
			if (!in_array($slot, $except, true) && $inventory->getItem($slot)->isNull()) {
				$inventory->setItem($slot, $filler);
			}
		}
	}

	/** @param array<string, scalar> $replacements */
	public static function text(string $key, string $default, array $replacements = []): string
	{
		$messages = Main::getInstance()->getMessages();
		return PlaceholderApi::parse($messages->format($messages->get($key, $default), null, $replacements));
	}
}
