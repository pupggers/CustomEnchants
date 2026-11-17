<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantGuard;
use pup\customenchants\utils\EnchantScan;

abstract class ToolEnchant extends CustomEnchant
{
	public static function onBreak(BlockBreakEvent $event): void
	{
		if ($event->isCancelled()) {
			return;
		}
		$player = $event->getPlayer();

		EnchantGuard::run('break:' . $player->getId(), static function () use ($event, $player): void {
			$item = $event->getItem();
			foreach (EnchantScan::of($item, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
				$enchant->execute($event->getPlayer(), $item, $event->getBlock(), $event, $level);
			}
		});
	}

	abstract public function execute(Player $player, Item $item, Block $block, ?Event $event, int $level): void;
}
