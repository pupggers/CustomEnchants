<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pup\customenchants\types\InteractEnchant;

class LaunchEnchant extends InteractEnchant
{
	//TODO: Make config
    private const float COOLDOWN_SECONDS = 5.0;

    /** @var array<int, float> entity id => time the player may launch again */
    private static array $readyAt = [];

    public function execute(Player $player, Item $item, ?Block $block, int $level): void
    {
        $now = microtime(true);
        if ((self::$readyAt[$player->getId()] ?? 0.0) > $now) {
            return;
        }
        self::$readyAt[$player->getId()] = $now + self::COOLDOWN_SECONDS;

        $direction = $player->getDirectionVector();
        $power = 0.8 + 0.3 * $level;
        $player->setMotion(new Vector3($direction->x * $power, 0.5 + 0.1 * $level, $direction->z * $power));
    }
}
