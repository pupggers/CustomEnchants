<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\ArrowEnchants;
use pup\customenchants\utils\EnchantScan;

abstract class ProjectileEnchant extends CustomEnchant
{
	public static function onHitEntity(EntityDamageByChildEntityEvent $event): void
	{
		if ($event->isCancelled()) {
			return;
		}
		$arrow = $event->getChild();
		$shooter = $event->getDamager();
		$victim = $event->getEntity();
		if (!$arrow instanceof Arrow || !$shooter instanceof Player || !$victim instanceof Living) {
			return;
		}
		$bow = ArrowEnchants::bowOf($arrow);
		if ($bow === null) {
			return;
		}
		foreach (EnchantScan::of($bow, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
			$enchant->execute($shooter, $bow, $arrow, $victim, $event, $level);
		}
	}

	abstract public function execute(
		Player $shooter,
		Item $bow,
		Arrow $arrow,
		Living $victim,
		EntityDamageByChildEntityEvent $event,
		int $level
	): void;
}