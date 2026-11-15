<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

final class RarityConfig
{
	/** @var array<string, array{color: string, weight: int}> */
	private const array DEFAULTS = [
		'COMMON' => ['color' => '{gray}', 'weight' => 50],
		'UNCOMMON' => ['color' => '{green}', 'weight' => 25],
		'RARE' => ['color' => '{blue}', 'weight' => 15],
		'EPIC' => ['color' => '{light_purple}', 'weight' => 10],
		'LEGENDARY' => ['color' => '{gold}', 'weight' => 5],
		'MASTERY' => ['color' => '{aqua}', 'weight' => 3],
		'HEROIC' => ['color' => '{red}', 'weight' => 0],
	];

	/** @var array<string, array{color: string, weight: int, enabled: bool}> */
	private static array $settings = [];

	/** @param array<string, mixed> $config */
	public static function init(array $config): void
	{
		self::$settings = [];
		foreach (Rarity::cases() as $rarity) {
			$section = $config[$rarity->name] ?? [];
			$section = is_array($section) ? $section : [];
			$defaults = self::DEFAULTS[$rarity->name];

			self::$settings[$rarity->name] = [
				'color' => (string) ($section['display_color'] ?? $defaults['color']),
				'weight' => (int) ($section['weight'] ?? $defaults['weight']),
				'enabled' => (bool) ($section['enabled'] ?? true),
			];
		}
	}

	public static function color(Rarity $rarity): string
	{
		return self::$settings[$rarity->name]['color'] ?? self::DEFAULTS[$rarity->name]['color'];
	}

	public static function weight(Rarity $rarity): int
	{
		return self::$settings[$rarity->name]['weight'] ?? self::DEFAULTS[$rarity->name]['weight'];
	}

	public static function isEnabled(Rarity $rarity): bool
	{
		return self::$settings[$rarity->name]['enabled'] ?? true;
	}
}