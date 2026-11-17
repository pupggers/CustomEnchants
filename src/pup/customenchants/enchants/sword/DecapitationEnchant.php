<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class DecapitationEnchant extends ChanceWeaponEnchant
{
    protected function canTarget(Living $victim): bool
    {
        return $victim instanceof Player;
    }

    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        if (!$victim instanceof Player || $victim->isAlive()) {
            return;
        }
        $head = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::PLAYER)->asItem();
        $head->setCustomName("§r§f" . $victim->getName() . "'s Head");
        $victim->getWorld()->dropItem($victim->getPosition(), $head);
    }
}
