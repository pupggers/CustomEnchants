<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\boots;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class WingsEnchant extends ToggledArmorEnchant
{
    public function onEquip(Player $player, Item $item, int $level, int $slot): void
    {
        $player->setAllowFlight(true);
    }

    public function onDequip(Player $player, Item $item, int $level, int $slot): void
    {
        if ($player->isCreative() || $player->isSpectator()) {
            return;
        }
        $player->setFlying(false);
        $player->setAllowFlight(false);
    }
}
