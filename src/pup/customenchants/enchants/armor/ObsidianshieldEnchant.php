<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class ObsidianshieldEnchant extends ToggledArmorEnchant
{
    public function onEquip(Player $player, Item $item, int $level): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 2147483647, 0, true, false, null, true));
    }

    public function onDequip(Player $player, Item $item, int $level): void
    {
        $player->getEffects()->remove(VanillaEffects::FIRE_RESISTANCE());
    }
}
