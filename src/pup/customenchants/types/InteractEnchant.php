<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantScan;

abstract class InteractEnchant extends CustomEnchant
{
	public static function onInteract(Player $player, Item $item, ?Block $block): void
	{
		foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self, $player) as [$enchant, $level]) {
			$enchant->execute($player, $item, $block, $level);
		}
	}

	abstract public function execute(Player $player, Item $item, ?Block $block, int $level): void;
}