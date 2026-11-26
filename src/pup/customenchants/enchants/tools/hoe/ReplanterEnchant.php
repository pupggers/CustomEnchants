<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\hoe;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Crops;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use pup\customenchants\utils\EnchantTasks;

class ReplanterEnchant extends ToolEnchant
{
    public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level): void
    {
        if (!$block instanceof Crops || $block->getAge() < Crops::MAX_AGE) {
            return;
        }
        $position = $block->getPosition();
        $world = $position->getWorld();
        $replacement = (clone $block)->setAge(0);
        EnchantTasks::later(1, static function () use ($world, $position, $replacement): void {
            if ($world->isLoaded() && $world->getBlock($position)->getTypeId() === BlockTypeIds::AIR) {
                $world->setBlock($position, $replacement);
            }
        });
    }
}
