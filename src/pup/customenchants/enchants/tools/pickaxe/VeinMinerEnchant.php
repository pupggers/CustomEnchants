<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\pickaxe;

use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use pup\customenchants\utils\MultiBreak;

class VeinMinerEnchant extends ToolEnchant
{
    public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level): void
    {
        if (MultiBreak::isActive($player) || !$this->isOre($block)) {
            return;
        }
        $limit = 8 + 8 * $level;
        $world = $block->getPosition()->getWorld();
        $start = $block->getPosition();

        $seen = [$this->key($start->getFloorX(), $start->getFloorY(), $start->getFloorZ()) => true];
        $queue = [$block];
        $found = [];
        while ($queue !== [] && count($found) < $limit) {
            $current = array_shift($queue)->getPosition();
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dz = -1; $dz <= 1; $dz++) {
                        $x = $current->getFloorX() + $dx;
                        $y = $current->getFloorY() + $dy;
                        $z = $current->getFloorZ() + $dz;
                        if (isset($seen[$this->key($x, $y, $z)])) {
                            continue;
                        }
                        $seen[$this->key($x, $y, $z)] = true;
                        $neighbour = $world->getBlockAt($x, $y, $z);
                        if ($neighbour->hasSameTypeId($block)) {
                            $found[] = $neighbour;
                            $queue[] = $neighbour;
                        }
                    }
                }
            }
        }
        MultiBreak::breakBlocks($player, $item, array_slice($found, 0, $limit));
    }

    private function isOre(Block $block): bool
    {
        $name = $block->getName();
        return str_ends_with($name, 'Ore') || $name === 'Ancient Debris';
    }

    private function key(int $x, int $y, int $z): string
    {
        return $x . ':' . $y . ':' . $z;
    }
}
