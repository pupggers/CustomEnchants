<?php


namespace pup\customenchants\enchants\armor\boots;


use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;

class GearsEnchant extends ToggledArmorEnchant
{

    public function onEquip(Player $player, Item $item, int $level)
    : void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 2147483647, max(0, $level - 1), true, false, null, true));
    }

	public function onDequip(Player $player, Item $item, int $level)
    : void
    {
        if ($player->getEffects()->has(VanillaEffects::SPEED())) {
            $player->getEffects()->remove(VanillaEffects::SPEED());
        }
    }
}