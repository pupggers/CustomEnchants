<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\world\particle\CriticalParticle;
use pup\customenchants\types\ChanceWeaponEnchant;
use Random\RandomException;

class EpicnessEnchant extends ChanceWeaponEnchant
{
    protected function requiresRoll(): bool
    {
        return false;
    }

	/**
	 * @throws RandomException
	 */
	protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $world = $victim->getWorld();
        $center = $victim->getPosition();
        for ($i = 0; $i < $level * 4; $i++) {
            $world->addParticle(
                $center->add(random_int(-5, 5) / 10, random_int(2, 18) / 10, random_int(-5, 5) / 10),
                new CriticalParticle()
            );
        }
    }
}
