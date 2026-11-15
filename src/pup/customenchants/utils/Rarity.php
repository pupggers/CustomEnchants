<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

enum Rarity: int
{
	case COMMON = 1;
	case UNCOMMON = 2;
	case RARE = 3;
	case EPIC = 4;
	case LEGENDARY = 5;
	case MASTERY = 6;
	case HEROIC = 7;

	/** @param array<string, mixed> $config the "rarities" section of config.yml */
	public static function init(array $config): void
	{
		RarityConfig::init($config);
	}

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
		return ucfirst(strtolower($this->name));
	}

	public function color(): string
	{
		return RarityConfig::color($this);
	}

	public function weight(): int
	{
		return RarityConfig::weight($this);
	}

	public function isEnabled(): bool
	{
		return RarityConfig::isEnabled($this);
	}
}