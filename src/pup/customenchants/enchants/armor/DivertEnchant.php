<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\RandomArmorEnchant;
use Random\RandomException;

class DivertEnchant extends RandomArmorEnchant
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

        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * (2 + $level), 0));
		$damager?->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * (2 + $level), 0));
    }
}
