<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\boots;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class SpringsEnchant extends ToggledArmorEnchant
{
    public function onEquip(Player $player, Item $item, int $level): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 2147483647, max(0, $level - 1), true, false, null, true));
    }

    public function onDequip(Player $player, Item $item, int $level): void
    {
        $player->getEffects()->remove(VanillaEffects::JUMP_BOOST());
    }
}
