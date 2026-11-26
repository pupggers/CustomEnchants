<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\axe;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use pup\customenchants\utils\MultiBreak;
use Random\RandomException;

class TimberEnchant extends ToolEnchant
{
	/**
	 * @param Player $player
	 * @param Item $item
	 * @param Block $block
	 * @param Event|null $event
	 * @param int $level
	 * @throws RandomException
	 */
	public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level): void
    {
        if (MultiBreak::isActive($player) || !$block instanceof Wood) {
            return;
        }
        $level = $item->getEnchantment($this)?->getLevel() ?? 1;
        if (random_int(1, 100) > $this->getChance($level, $this->getMaxLevel())) {
            return;
        }

        $limit = 32 * $level;
        $world = $block->getPosition()->getWorld();
        $start = $block->getPosition();
        $seen = [$start->getFloorX() . ':' . $start->getFloorY() . ':' . $start->getFloorZ() => true];
        $queue = [$block];
        $found = [];
        while ($queue !== [] && count($found) < $limit) {
            $current = array_shift($queue)->getPosition();
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = 0; $dy <= 1; $dy++) {
                    for ($dz = -1; $dz <= 1; $dz++) {
                        $x = $current->getFloorX() + $dx;
                        $y = $current->getFloorY() + $dy;
                        $z = $current->getFloorZ() + $dz;
                        $key = $x . ':' . $y . ':' . $z;
                        if (isset($seen[$key])) {
                            continue;
                        }
                        $seen[$key] = true;
                        $neighbour = $world->getBlockAt($x, $y, $z);
                        if ($neighbour instanceof Wood && $neighbour->hasSameTypeId($block)) {
                            $found[] = $neighbour;
                            $queue[] = $neighbour;
                        }
                    }
                }
            }
        }
        MultiBreak::breakBlocks($player, $item, array_slice($found, 0, $limit));
    }
}
