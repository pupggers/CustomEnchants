<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class HelmetSwap
{
	/** @var array<int, array{helmet: Item, standIn: Item}> */
	private static array $swapped = [];

	public static function isSwapped(Player $player): bool
	{
		return isset(self::$swapped[$player->getId()]);
	}

	public static function swap(Player $player, Item $standIn, int $ticks): bool
	{
		if (self::isSwapped($player)) {
			return false;
		}
		$armor = $player->getArmorInventory();
		self::$swapped[$player->getId()] = ['helmet' => $armor->getHelmet(), 'standIn' => clone $standIn];
		$armor->setHelmet($standIn);
		EnchantTasks::later($ticks, static function () use ($player): void {
			self::restore($player);
		});
		return true;
	}

	public static function restore(Player $player): void
	{
		$entry = self::$swapped[$player->getId()] ?? null;
		if ($entry === null) {
			return;
		}
		unset(self::$swapped[$player->getId()]);
		if (!$player->isConnected()) {
			return;
		}
		$armor = $player->getArmorInventory();
		$current = $armor->getHelmet();
		if ($current->isNull() || $current->equals($entry['standIn'], false, false)) {
			$armor->setHelmet($entry['helmet']);
			return;
		}
		foreach ($player->getInventory()->addItem($entry['helmet']) as $rest) {
			$player->getWorld()->dropItem($player->getPosition(), $rest);
		}
	}

	public static function onDeath(PlayerDeathEvent $event): void
	{
		$player = $event->getPlayer();
		$entry = self::$swapped[$player->getId()] ?? null;
		if ($entry === null) {
			return;
		}
		if ($event->getKeepInventory()) {
			self::restore($player);
			return;
		}
		unset(self::$swapped[$player->getId()]);
		$drops = $event->getDrops();
		foreach ($drops as $i => $drop) {
			if ($drop->equals($entry['standIn'], false, false)) {
				unset($drops[$i]);
				break;
			}
		}
		$drops[] = $entry['helmet'];
		$event->setDrops(array_values($drops));
	}
}
