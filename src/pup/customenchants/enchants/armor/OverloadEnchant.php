<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class OverloadEnchant extends ToggledArmorEnchant
{
    public function onEquip(Player $player, Item $item, int $level, int $slot): void
    {
        $this->setHealthBonus($player, $slot, 2 * $level);
    }

    public function onDequip(Player $player, Item $item, int $level, int $slot): void
    {
        $this->setHealthBonus($player, $slot, 0);
    }
}