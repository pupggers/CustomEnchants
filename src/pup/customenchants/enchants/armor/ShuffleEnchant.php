<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\item\Armor;
use pocketmine\player\Player;
use pocketmine\world\sound\EndermanTeleportSound;
use pup\customenchants\types\RandomArmorEnchant;

class ShuffleEnchant extends RandomArmorEnchant
{
	public function execute(Player $player, ?Player $damager, Armor $armor, int $level): void
	{
		if ($damager === null || !$this->roll($level)) {
			return;
		}

		$this->shuffleHotbar($damager);
	}

	private function shuffleHotbar(Player $target): void
	{
		$inventory = $target->getInventory();

		$hotbar = [];
		for ($slot = 0; $slot <= 8; $slot++) {
			$item = $inventory->getItem($slot);
			if (!$item->isNull()) {
				$hotbar[$slot] = $item;
			}
		}

		if (count($hotbar) < 2) {
			return;
		}

		$slots = array_keys($hotbar);
		$items = array_values($hotbar);
		shuffle($items);

		foreach ($slots as $index => $slot) {
			$inventory->setItem($slot, $items[$index]);
		}

		$target->getWorld()->addSound($target->getPosition(), new EndermanTeleportSound());
	}
}