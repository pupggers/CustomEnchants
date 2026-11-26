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

class DisappearEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($player->getHealth() > $player->getMaxHealth() * 0.3 || !$this->roll($level)) {
            return;
        }
        $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 20 * (3 + $level), 0));
    }
}
