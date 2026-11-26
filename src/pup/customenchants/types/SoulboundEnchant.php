<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantScan;

class SoulboundEnchant extends CustomEnchant
{
	/** @var array<string, array{items: list<Item>, position: Position}> player xuid => parked items */
	private static array $parked = [];

	public static function onDeath(PlayerDeathEvent $event): void
	{
		if ($event->getKeepInventory()) {
			return;
		}

		$kept = [];
		$drops = [];
		foreach ($event->getDrops() as $item) {
			if (self::shouldKeep($item)) {
				$kept[] = $item;
			} else {
				$drops[] = $item;
			}
		}
		if ($kept === []) {
			return;
		}

		$player = $event->getPlayer();
		$event->setDrops($drops);
		self::$parked[$player->getUniqueId()->toString()] = [
			'items' => $kept,
			'position' => $player->getPosition(),
		];
	}

	/** Call on respawn. */
	public static function restore(Player $player): void
	{
		$id = $player->getXuid();
		$entry = self::$parked[$id] ?? null;
		if ($entry === null) {
			return;
		}
		unset(self::$parked[$id]);

		foreach ($player->getInventory()->addItem(...$entry['items']) as $leftover) {
			$player->getWorld()->dropItem($player->getPosition(), $leftover);
		}
	}

	public static function onQuit(Player $player): void
	{
		$id = $player->getXuid();
		$entry = self::$parked[$id] ?? null;
		if ($entry === null) {
			return;
		}
		unset(self::$parked[$id]);

		$position = $entry['position'];
		if (!$position->isValid()) {
			return;
		}
		foreach ($entry['items'] as $item) {
			$position->getWorld()->dropItem($position, $item);
		}
	}

	private static function shouldKeep(Item $item): bool
	{
		foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
			if ($enchant->roll($level)) {
				return true;
			}
		}
		return false;
	}
}