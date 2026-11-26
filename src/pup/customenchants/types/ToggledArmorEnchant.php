<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\effect\Effect;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\ArmorEffects;
use pup\customenchants\utils\EnchantScan;

abstract class ToggledArmorEnchant extends CustomEnchant
{
	/** @var array<string, CallbackInventoryListener> */
	private static array $listeners = [];

	/** @var array<string, array<string, array{0: self, 1: int, 2: Item, 3: int}>> */
	private static array $active = [];

	abstract public function onEquip(Player $player, Item $item, int $level, int $slot): void;

	abstract public function onDequip(Player $player, Item $item, int $level, int $slot): void;

	public function onArmorUpdate(Player $player, Item $item, int $level, int $slot): void
	{
	}

	protected function grantEffect(Player $player, int $slot, Effect $effect, int $amplifier): void
	{
		ArmorEffects::grant($player, static::class . '#' . $slot, $effect, $amplifier);
	}

	protected function revokeEffect(Player $player, int $slot, Effect $effect): void
	{
		ArmorEffects::revoke($player, static::class . '#' . $slot, $effect);
	}

	protected function setHealthBonus(Player $player, int $slot, int $bonus): void
	{
		ArmorEffects::setHealthBonus($player, static::class . '#' . $slot, $bonus);
	}

	/** @return array<int, int> */
	public static function getWornLevels(Player $player, self $enchant): array
	{
		$levels = [];
		foreach (self::$active[$player->getXuid()] ?? [] as [$active, $level, , $slot]) {
			if ($active === $enchant) {
				$levels[$slot] = $level;
			}
		}
		return $levels;
	}

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
		foreach (self::$active[$id] ?? [] as [$enchant, $level, $item, $slot]) {
			$enchant->onDequip($player, $item, $level, $slot);
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
		foreach ($player->getArmorInventory()->getContents() as $slot => $item) {
			foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
				$now[$slot . ':' . spl_object_id($enchant)] = [$enchant, $level, $item, $slot];
			}
		}
		$before = self::$active[$id] ?? [];

		$changed = false;
		foreach ($before as $key => [$enchant, $level, $item, $slot]) {
			if (!isset($now[$key]) || $now[$key][1] !== $level) {
				$enchant->onDequip($player, $item, $level, $slot);
				$changed = true;
			}
		}
		self::$active[$id] = $now;
		foreach ($now as $key => [$enchant, $level, $item, $slot]) {
			if (!isset($before[$key]) || $before[$key][1] !== $level) {
				$enchant->onEquip($player, $item, $level, $slot);
				$changed = true;
			}
		}

		if ($changed) {
			foreach ($now as [$enchant, $level, $item, $slot]) {
				$enchant->onArmorUpdate($player, $item, $level, $slot);
			}
		}
	}
}
