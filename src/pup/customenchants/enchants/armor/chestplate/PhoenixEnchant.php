<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\chestplate;

use pocketmine\entity\animation\TotemUseAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pocketmine\world\sound\TotemUseSound;
use pup\customenchants\types\DefenseArmorEnchant;

class PhoenixEnchant extends DefenseArmorEnchant
{
	//TODO Make config.
    private const COOLDOWN_SECONDS = 90;

    /** @var array<int, int> entity id => unix time the enchant is ready again */
    private static array $readyAt = [];

    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {
        if ($event->getFinalDamage() < $player->getHealth()) {
            return; // not a lethal hit
        }
        $now = time();
        if ((self::$readyAt[$player->getId()] ?? 0) > $now || !$this->roll($level)) {
            return;
        }
        self::$readyAt[$player->getId()] = $now + self::COOLDOWN_SECONDS;

        $event->cancel();
        $player->setHealth($player->getMaxHealth());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 5, 3));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 5, 1));
        $player->broadcastAnimation(new TotemUseAnimation($player));
        $player->getWorld()->addSound($player->getPosition(), new TotemUseSound());
    }
}
