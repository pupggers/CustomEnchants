<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use Closure;
use Generator;
use pocketmine\item\Item;

final class EnchantScan
{
	/**
	 * @param Closure(object): bool $filter
	 * @return Generator<int, array{0: object, 1: int}> [enchant, level]
	 */
	public static function of(Item $item, Closure $filter): Generator
	{
		foreach ($item->getEnchantments() as $instance) {
			$enchant = $instance->getType();
			if ($filter($enchant)) {
				yield [$enchant, $instance->getLevel()];
			}
		}
	}
}