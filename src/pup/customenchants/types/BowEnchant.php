<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\ArrowEnchants;
use pup\customenchants\utils\EnchantScan;

abstract class BowEnchant extends CustomEnchant
{
	public static function onShoot(EntityShootBowEvent $event): void
	{
		ArrowEnchants::onShoot($event);
	}

	public static function onHitBlock(ProjectileHitBlockEvent $event): void
	{
		$arrow = $event->getEntity();
		if (!$arrow instanceof Arrow) {
			return;
		}
		$shooter = $arrow->getOwningEntity();
		$bow = ArrowEnchants::bowOf($arrow);
		if (!$shooter instanceof Player || $bow === null) {
			return;
		}
		foreach (EnchantScan::of($bow, static fn(object $e): bool => $e instanceof self, $shooter) as [$enchant, $level]) {
			$enchant->execute($shooter, $bow, $arrow, $level);
		}
	}

	abstract public function execute(Player $player, Item $bow, Arrow $arrow, int $level): void;
}
