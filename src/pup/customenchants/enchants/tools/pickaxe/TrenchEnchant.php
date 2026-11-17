<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\pickaxe;

use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use pup\customenchants\utils\MultiBreak;
use Random\RandomException;

class TrenchEnchant extends ToolEnchant
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
        if (MultiBreak::isActive($player)) {
            return;
        }
        if (random_int(1, 100) > $this->getChance($level, $this->getMaxLevel())) {
            return;
        }

        $radius = $level >= 7 ? 2 : 1;
        $origin = $block->getPosition();
        $world = $origin->getWorld();
        $look = $player->getDirectionVector();
        $steep = abs($player->getLocation()->getPitch()) > 45;

        $targets = [];
        for ($a = -$radius; $a <= $radius; $a++) {
            for ($b = -$radius; $b <= $radius; $b++) {
                if ($a === 0 && $b === 0) {
                    continue;
                }
                if ($steep) {
                    [$dx, $dy, $dz] = [$a, 0, $b];
                } elseif (abs($look->x) > abs($look->z)) {
                    [$dx, $dy, $dz] = [0, $a, $b];
                } else {
                    [$dx, $dy, $dz] = [$a, $b, 0];
                }
                $target = $world->getBlockAt($origin->getFloorX() + $dx, $origin->getFloorY() + $dy, $origin->getFloorZ() + $dz);
                if ($target->getBreakInfo()->isToolCompatible($item)) {
                    $targets[] = $target;
                }
            }
        }
        MultiBreak::breakBlocks($player, $item, $targets);
    }
}
