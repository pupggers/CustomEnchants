<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantGuard;
use pup\customenchants\utils\EnchantScan;

abstract class DefenseArmorEnchant extends CustomEnchant
{
	public const int MODIFIER_ID = 0x5550;

	public static function onDamage(EntityDamageEvent $event): void
	{
		$player = $event->getEntity();
		if (!$player instanceof Player || $event->isCancelled()) {
			return;
		}
		$damager = $event instanceof EntityDamageByEntityEvent ? $event->getDamager() : null;

		EnchantGuard::run('defense:' . $player->getId(), static function () use ($player, $damager, $event): void {
			foreach ($player->getArmorInventory()->getContents() as $armor) {
				if (!$armor instanceof Armor) {
					continue;
				}
				foreach (EnchantScan::of($armor, static fn(object $e): bool => $e instanceof self, $player) as [$enchant, $level]) {
					$enchant->execute($player, $damager, $armor, $event, $level);
					if ($event->isCancelled()) {
						return;
					}
				}
			}
		});
	}

	abstract public function execute(
		Player $player,
		?Entity $damager,
		Armor $armor,
		EntityDamageEvent $event,
		int $level
	): void;

	protected function reduceDamage(EntityDamageEvent $event, float $fraction): void
	{
		$fraction = max(0.0, min(1.0, $fraction));
		$event->setModifier(
			$event->getModifier(self::MODIFIER_ID) - $event->getFinalDamage() * $fraction,
			self::MODIFIER_ID
		);
	}
}