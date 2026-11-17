<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class FuddleEnchant extends ChanceWeaponEnchant
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
        $inventory = $victim->getInventory();
        $hotbar = [];
        for ($slot = 0; $slot < $inventory->getHotbarSize(); $slot++) {
            $hotbar[$slot] = $inventory->getItem($slot);
        }
        $shuffled = $hotbar;
        shuffle($shuffled);
        foreach ($shuffled as $slot => $item) {
            $inventory->setItem($slot, $item);
        }
    }
}
