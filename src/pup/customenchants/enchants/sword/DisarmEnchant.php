<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class DisarmEnchant extends ChanceWeaponEnchant
{
    protected function canTarget(Living $victim): bool
    {
        return $victim instanceof Player;
    }

    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        if (!$victim instanceof Player) {
            return;
        }
        $held = $victim->getInventory()->getItemInHand();
        if ($held->isNull()) {
            return;
        }
        $victim->getInventory()->setItemInHand(VanillaItems::AIR());
        $victim->getWorld()->dropItem($victim->getPosition(), $held, null, 40);
    }
}
