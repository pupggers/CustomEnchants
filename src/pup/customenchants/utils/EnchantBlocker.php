<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use Closure;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\Server;

final class EnchantBlocker
{
	/** @var array<int, list<array{until: int, filter: ?Closure}>> */
	private static array $blocks = [];

	/** @param Closure(Enchantment): bool|null $filter null blocks every custom enchant */
	public static function block(Entity $target, int $ticks, ?Closure $filter = null): void
	{
		self::$blocks[$target->getId()][] = [
			'until' => Server::getInstance()->getTick() + max(1, $ticks),
			'filter' => $filter,
		];
	}

	public static function isBlocked(?Entity $holder, Enchantment $enchant): bool
	{
		if ($holder === null || !isset(self::$blocks[$holder->getId()])) {
			return false;
		}

		$now = Server::getInstance()->getTick();
		$active = array_values(array_filter(
			self::$blocks[$holder->getId()],
			static fn(array $block): bool => $block['until'] > $now
		));

		if ($active === []) {
			unset(self::$blocks[$holder->getId()]);
			return false;
		}
		self::$blocks[$holder->getId()] = $active;

		foreach ($active as $block) {
			if ($block['filter'] === null || ($block['filter'])($enchant)) {
				return true;
			}
		}
		return false;
	}

	public static function clear(Entity $target): void
	{
		unset(self::$blocks[$target->getId()]);
	}
}
