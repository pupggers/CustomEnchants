<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pup\customenchants\EnchantManager;

enum Rarity: int
{
	case COMMON = 1;
	case UNCOMMON = 2;
	case RARE = 3;
	case EPIC = 4;
	case LEGENDARY = 5;
	// Value 8 so existing items keep their stored rarities; listed here for tier order.
	case SOUL = 8;
	case MASTERY = 6;
	case HEROIC = 7;

	public static function fromValue(int $value): self
	{
		return self::tryFrom($value) ?? self::UNCOMMON;
	}

	public static function fromName(string $name): self
	{
		$name = strtoupper($name);
		foreach (self::cases() as $case) {
			if ($case->name === $name && $case->isEnabled()) {
				return $case;
			}
		}
		return self::UNCOMMON;
	}

	/** @return list<self> */
	public static function enabled(): array
	{
		return array_values(array_filter(self::cases(), static fn(self $r): bool => $r->isEnabled()));
	}

	public function label(): string
	{
		return EnchantManager::rarityName($this);
	}

	public function color(): string { return EnchantManager::rarityColor($this); }
	public function weight(): int { return EnchantManager::rarityWeight($this); }
	public function isEnabled(): bool { return EnchantManager::isRarityEnabled($this); }
}