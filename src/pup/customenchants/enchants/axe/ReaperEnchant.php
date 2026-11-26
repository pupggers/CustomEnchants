<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\axe;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class ReaperEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $victim->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * (2 + $level), 0));
        $victim->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * (2 + $level), 0));
    }
}
