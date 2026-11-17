<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class DisarmorEnchant extends ChanceWeaponEnchant
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
        $armorInventory = $victim->getArmorInventory();
        $worn = $armorInventory->getContents();
        if ($worn === []) {
            return;
        }
        $slot = array_rand($worn);
        $piece = $worn[$slot];
        $armorInventory->clear($slot);
        foreach ($victim->getInventory()->addItem($piece) as $leftover) {
            $victim->getWorld()->dropItem($victim->getPosition(), $leftover, null, 40);
        }
    }
}
