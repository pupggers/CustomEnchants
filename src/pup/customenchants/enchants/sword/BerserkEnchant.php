<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class BerserkEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $attacker->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * (3 + $level), $level >= 4 ? 1 : 0));
        $attacker->getEffects()->add(new EffectInstance(VanillaEffects::MINING_FATIGUE(), 20 * (3 + $level), 0));
    }
}
