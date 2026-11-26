<?php


namespace pup\customenchants\enchants\tools;


use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use Random\RandomException;

class ExperienceEnchant extends ToolEnchant
{

    /**
	 * @param Player $player
	 * @param Item $item
	 * @param Block $block
	 * @param Event|null $event
	 * @param int $level
     * @throws RandomException
     */
    public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level)
    : void
    {
        $chance = $this->getChance($level, $this->getMaxLevel());

        if(random_int(1, 100) <= $chance){
            $player->getXpManager()->addXp((random_int(1, 10) * random_int(1, 5)));
        }
    }
}