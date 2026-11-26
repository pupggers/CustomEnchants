<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\RandomArmorEnchant;
use Random\RandomException;

class MoltenEnchant extends RandomArmorEnchant
{
	/**
	 * @param Player $player
	 * @param Player|null $damager
	 * @param Armor $armor
	 * @param int $level
	 * @throws RandomException
	 */
	public function execute(Player $player, ?Player $damager, Armor $armor, int $level): void
    {
		if(!$this->roll($level)) return;

		$damager?->setOnFire(2 + $level);
    }
}
