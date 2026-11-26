<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class PermafrostEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * (2 + $level), 1));
        $this->bleed($attacker, $victim, 2 + $level);
    }
}
