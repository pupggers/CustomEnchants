<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;

class TelepathyEnchant extends ToolEnchant
{
    public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level): void
    {
		//This should run last
        if (!$event instanceof BlockBreakEvent) {
            return;
        }
        $position = $block->getPosition()->add(0.5, 0.5, 0.5);
        $drops = $event->getDrops();
        if ($drops !== []) {
            foreach ($player->getInventory()->addItem(...$drops) as $leftover) {
                $block->getPosition()->getWorld()->dropItem($position, $leftover);
            }
            $event->setDrops([]);
        }
        $xp = $event->getXpDropAmount();
        if ($xp > 0) {
            $player->getXpManager()->addXp($xp);
            $event->setXpDropAmount(0);
        }
    }
}
