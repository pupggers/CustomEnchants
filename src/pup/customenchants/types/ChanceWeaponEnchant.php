<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pup\customenchants\utils\EnchantBlocker;
use pup\customenchants\utils\EnchantTasks;

abstract class ChanceWeaponEnchant extends WeaponEnchant
{
	final public function isApplicableTo(Entity $victim): bool
	{
		return $victim instanceof Living && $this->canTarget($victim);
	}

	protected function canTarget(Living $victim): bool
	{
		return true;
	}

	protected function requiresRoll(): bool
	{
		return true;
	}

	public function onPostAttack(Entity $attacker, Entity $victim, int $enchantmentLevel): void
	{
		if (!$attacker instanceof Player || !$victim instanceof Living || EnchantBlocker::isBlocked($attacker, $this)) {
			return;
		}
		if ($this->requiresRoll() && !$this->roll($enchantmentLevel)) {
			return;
		}
		$this->trigger($attacker, $victim, $enchantmentLevel);
	}

	abstract protected function trigger(Player $attacker, Living $victim, int $level): void;

	protected function dealExtraDamage(
		Player $attacker,
		Living $victim,
		float $damage,
		int $cause = EntityDamageEvent::CAUSE_CUSTOM,
		float $knockBack = 0.0
	): void {
		if (!$victim->isAlive()) {
			return;
		}
		$victim->attack(new EntityDamageByEntityEvent($attacker, $victim, $cause, $damage, [], $knockBack));
	}

	/**
	 * @return Living[]
	 */
	protected function nearby(Player $attacker, Living $victim, float $radius, bool $playersOnly = false): array
	{
		$found = [];
		$box = $victim->getBoundingBox()->expandedCopy($radius, $radius, $radius);
		foreach ($victim->getWorld()->getNearbyEntities($box) as $entity) {
			if (!$entity instanceof Living || $entity === $attacker || $entity === $victim || !$entity->isAlive()) {
				continue;
			}
			if ($playersOnly && !$entity instanceof Player) {
				continue;
			}
			$found[] = $entity;
		}
		return $found;
	}

	protected function bleed(Player $attacker, Living $victim, int $seconds, float $damage = 1.0): void
	{
		EnchantTasks::repeatTimes(20, $seconds, function () use ($attacker, $victim, $damage): bool {
			if ($victim->isClosed() || !$victim->isAlive() || $attacker->isClosed()) {
				return false;
			}
			$this->dealExtraDamage($attacker, $victim, $damage);
			return true;
		});
	}
}