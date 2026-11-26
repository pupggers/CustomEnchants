<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class ObsidianshieldEnchant extends ToggledArmorEnchant
{
    public function onEquip(Player $player, Item $item, int $level, int $slot): void
    {
        $this->grantEffect($player, $slot, VanillaEffects::FIRE_RESISTANCE(), 0);
    }

    public function onDequip(Player $player, Item $item, int $level, int $slot): void
    {
        $this->revokeEffect($player, $slot, VanillaEffects::FIRE_RESISTANCE());
    }
}
