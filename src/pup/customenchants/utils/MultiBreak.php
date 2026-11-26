<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class MultiBreak
{
    /** @var array<int, true> */
    private static array $active = [];

    public static function isActive(Player $player): bool
    {
        return isset(self::$active[$player->getId()]);
    }

    /**
     * @param Block[] $blocks
     * @return int number of blocks actually broken
     */
    public static function breakBlocks(Player $player, Item $tool, array $blocks): int
    {
        if (self::isActive($player)) {
            return 0;
        }
        self::$active[$player->getId()] = true;
        $broken = 0;
        try {
            $world = $player->getWorld();
            $workingTool = clone $tool;
            foreach ($blocks as $block) {
                if ($block->getTypeId() === BlockTypeIds::AIR || !$block->getBreakInfo()->isBreakable()) {
                    continue;
                }
                if ($world->useBreakOn($block->getPosition(), $workingTool, $player, true)) {
                    $broken++;
                }
            }
        } finally {
            unset(self::$active[$player->getId()]);
        }
        return $broken;
    }
}
