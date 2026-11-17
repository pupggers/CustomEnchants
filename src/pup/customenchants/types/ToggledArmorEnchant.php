<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantScan;

abstract class ToggledArmorEnchant extends CustomEnchant
{
	/** @var array<string, CallbackInventoryListener> */
	private static array $listeners = [];

	/** @var array<string, array<int, array{0: self, 1: int, 2: Item}>>*/
	private static array $active = [];

	public static function onToggle(InventoryTransactionEvent $event): void
	{
		self::ensureArmorListener($event->getTransaction()->getSource());
	}

	public static function ensureArmorListener(Player $player): void
	{
		$id = $player->getXuid();
		if (isset(self::$listeners[$id])) {
			return;
		}
		$listener = new CallbackInventoryListener(
			static function (Inventory $inventory, int $slot, Item $old): void {
				self::syncHolder($inventory);
			},
			static function (Inventory $inventory, array $oldContents): void {
				self::syncHolder($inventory);
			}
		);
		self::$listeners[$id] = $listener;
		$player->getArmorInventory()->getListeners()->add($listener);

		self::sync($player);
	}

	public static function removeArmorListener(Player $player): void
	{
		$id = $player->getXuid();
		$listener = self::$listeners[$id] ?? null;
		if ($listener !== null) {
			$player->getArmorInventory()->getListeners()->remove($listener);
		}
		foreach (self::$active[$id] ?? [] as [$enchant, $level, $item]) {
			$enchant->onDequip($player, $item, $level);
		}
		unset(self::$listeners[$id], self::$active[$id]);
	}

	private static function syncHolder(Inventory $inventory): void
	{
		if (!$inventory instanceof ArmorInventory) {
			return;
		}
		$holder = $inventory->getHolder();
		if ($holder instanceof Player) {
			self::sync($holder);
		}
	}

	private static function sync(Player $player): void
	{
		$id = $player->getXuid();

		$now = [];
		foreach ($player->getArmorInventory()->getContents() as $item) {
			foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
				$key = spl_object_id($enchant);
				if (!isset($now[$key]) || $level > $now[$key][1]) {
					$now[$key] = [$enchant, $level, $item];
				}
			}
		}
		$before = self::$active[$id] ?? [];

		foreach ($before as $key => [$enchant, $level, $item]) {
			if (!isset($now[$key]) || $now[$key][1] !== $level) {
				$enchant->onDequip($player, $item, $level);
			}
		}
		foreach ($now as $key => [$enchant, $level, $item]) {
			if (!isset($before[$key]) || $before[$key][1] !== $level) {
				$enchant->onEquip($player, $item, $level);
			}
		}
		self::$active[$id] = $now;
	}

	abstract public function onEquip(Player $player, Item $item, int $level): void;

	abstract public function onDequip(Player $player, Item $item, int $level): void;
}
