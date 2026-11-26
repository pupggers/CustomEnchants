<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\pickaxe;

use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use Random\RandomException;

class RebreatherEnchant extends ToolEnchant
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
        if ($player->isUnderwater() && random_int(1, 100) <= $this->getChance($level, $this->getMaxLevel())) {
            $player->setAirSupplyTicks($player->getMaxAirSupplyTicks());
        }
    }
}
