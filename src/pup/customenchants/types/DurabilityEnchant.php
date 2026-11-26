<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantScan;

abstract class DurabilityEnchant extends CustomEnchant
{
	private const string GRANTED_TAG = 'ce_unbreakable';

	/** @var array<string, list<array{0: Inventory, 1: CallbackInventoryListener}>> */
	private static array $listeners = [];

	/** @var array<string, true> re-entrancy guard: our own setItem() triggers the listener again */
	private static array $busy = [];

	abstract public function refund(int $level, int $damageTaken): int;

	public function makesUnbreakable(): bool
	{
		return false;
	}

	protected function rollEach(int $level, int $times): int
	{
		$saved = 0;
		for ($i = 0; $i < $times; $i++) {
			if ($this->roll($level)) {
				$saved++;
			}
		}
		return $saved;
	}

	public static function ensureListeners(Player $player): void
	{
		$id = $player->getXuid();

		if (isset(self::$listeners[$id])) {
			return;
		}
		self::$listeners[$id] = [];
		$inventories = [$player->getInventory(), $player->getArmorInventory()];

		foreach ($inventories as $inventory) {
			$listener = new CallbackInventoryListener(
				static function (Inventory $inv, int $slot, Item $old) use ($id): void {
					self::handleSlot($id, $inv, $slot, $old);
				},
				static function (Inventory $inv, array $oldContents) use ($id): void {
					foreach ($oldContents as $slot => $old) {
						self::handleSlot($id, $inv, $slot, $old);
					}
				}
			);
			$inventory->getListeners()->add($listener);
			self::$listeners[$id][] = [$inventory, $listener];
		}

		foreach ($inventories as $inventory) {
			foreach ($inventory->getContents() as $slot => $item) {
				self::handleSlot($id, $inventory, $slot, $item);
			}
		}
	}

	public static function removeListeners(Player $player): void
	{
		$id = $player->getXuid();
		foreach (self::$listeners[$id] ?? [] as [$inventory, $listener]) {
			$inventory->getListeners()->remove($listener);
		}
		unset(self::$listeners[$id], self::$busy[$id]);
	}

	private static function handleSlot(string $id, Inventory $inventory, int $slot, Item $old): void
	{
		if (isset(self::$busy[$id])) {
			return;
		}
		$item = $inventory->getItem($slot);
		if (!$item instanceof Durable) {
			return;
		}

		$damageTaken = 0;
		if ($old instanceof Durable && $old->equals($item, false, true)) {
			$damageTaken = max(0, $item->getDamage() - $old->getDamage());
		}

		$changed = false;
		$wantsUnbreakable = false;
		foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
			$wantsUnbreakable = $wantsUnbreakable || $enchant->makesUnbreakable();
			if ($damageTaken > 0) {
				$back = min($damageTaken, $enchant->refund($level, $damageTaken));
				if ($back > 0) {
					$item->setDamage($item->getDamage() - $back);
					$damageTaken -= $back;
					$changed = true;
				}
			}
		}

		$tag = $item->getNamedTag();
		$granted = $tag->getTag(self::GRANTED_TAG) !== null;

		if ($wantsUnbreakable && !$item->isUnbreakable()) {
			$item->setUnbreakable();
			$item->setNamedTag($tag->setByte(self::GRANTED_TAG, 1));
			$changed = true;
		} elseif (!$wantsUnbreakable && $granted) {
			$item->setUnbreakable(false);
			$tag->removeTag(self::GRANTED_TAG);
			$item->setNamedTag($tag);
			$changed = true;
		}

		if ($changed) {
			self::$busy[$id] = true;
			try {
				$inventory->setItem($slot, $item);
			} finally {
				unset(self::$busy[$id]);
			}
		}
	}
}
