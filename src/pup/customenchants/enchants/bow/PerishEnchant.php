<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\bow;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ProjectileEnchant;

class PerishEnchant extends ProjectileEnchant
{
    public function execute(Player $shooter, Item $bow, Arrow $arrow, Living $victim, EntityDamageByChildEntityEvent $event, int $level): void
    {
        if (!$this->roll($level)) {
            return;
        }
        $victim->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * (2 + $level), $level >= 4 ? 1 : 0));
    }
}
