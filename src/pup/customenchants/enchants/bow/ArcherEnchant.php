<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\bow;

use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ProjectileEnchant;

class ArcherEnchant extends ProjectileEnchant
{
    public function execute(Player $shooter, Item $bow, Arrow $arrow, Living $victim, EntityDamageByChildEntityEvent $event, int $level): void
    {
        $event->setBaseDamage($event->getBaseDamage() * (1 + 0.1 * $level));
    }
}
