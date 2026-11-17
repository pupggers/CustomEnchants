<?php

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class AronistEnchant extends ChanceWeaponEnchant
{
	protected function trigger(Player $attacker, Living $victim, int $level): void
	{
		if (!$victim->isOnFire()) {
			return;
		}

		$this->dealExtraDamage($attacker, $victim, $level * 1.5);
	}
}