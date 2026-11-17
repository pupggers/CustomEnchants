<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools;

use pocketmine\block\Block;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToolEnchant;
use Random\RandomException;

class HastenEnchant extends ToolEnchant
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
        if (random_int(1, 100) > $this->getChance($level, $this->getMaxLevel())) {
            return;
        }
        $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 20 * (2 + $level * 2), $level - 1));
    }
}
