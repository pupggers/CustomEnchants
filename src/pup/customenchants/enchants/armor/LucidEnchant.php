<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class LucidEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if (!$this->roll($level)) {
            return;
        }
        $player->getEffects()->remove(VanillaEffects::BLINDNESS());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * (5 + 3 * $level), 0));
    }
}
