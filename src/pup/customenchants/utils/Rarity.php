<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;

final class Rarity
{
	public const int COMMON = 1;
	public const int UNCOMMON = 2;
	public const int RARE = 3;
	public const int EPIC = 4;
	public const int LEGENDARY = 5;
	public const int MASTERY = 6;
	public const int HEROIC = 7;

	private const int FALLBACK = self::UNCOMMON;

	/** @var array<int, self> */
	private static array $byValue = [];

	/** @var array<string, self> */
	private static array $byName = [];

	/** @var array<string, array<string, mixed>> */
	private static array $overrides = [];

	private function __construct(
		public readonly string $name,
		public readonly int $value,
		private readonly string $label,
		private readonly string $color,
		public readonly int $weight,
		private readonly bool $enabled,
		public readonly ?Plugin $owner,
	) {}

	/** @param array $overrides config.yml `rarities`, keyed by rarity name */
	public static function init(array $overrides): void
	{
		self::$byValue = [];
		self::$byName = [];
		self::$overrides = [];
		foreach ($overrides as $name => $section) {
			if (is_string($name) && is_array($section)) {
				self::$overrides[strtoupper($name)] = $section;
			}
		}

		self::register('COMMON', self::COMMON, 'Common', '{gray}', 50);
		self::register('UNCOMMON', self::UNCOMMON, 'Uncommon', '{green}', 25);
		self::register('RARE', self::RARE, 'Rare', '{blue}', 15);
		self::register('EPIC', self::EPIC, 'Epic', '{light_purple}', 10);
		self::register('LEGENDARY', self::LEGENDARY, 'Legendary', '{gold}', 5);
		self::register('MASTERY', self::MASTERY, 'Mastery', '{red}', 3);
		self::register('HEROIC', self::HEROIC, 'Heroic', '{dark_red}');
	}

	/**
	 * @param int $value stored on items, so it must never change once in use
	 * @throws InvalidArgumentException when the name or value is taken
	 */
	public static function register(string $name, int $value, string $label, string $color, int $weight = 0, ?Plugin $owner = null): self
	{
		$name = strtoupper($name);
		if (preg_match('/^[A-Z][A-Z0-9_]*$/', $name) !== 1) {
			throw new InvalidArgumentException("Rarity name '$name' must be letters, digits and underscores.");
		}
		if (isset(self::$byName[$name])) {
			throw new InvalidArgumentException("Rarity '$name' is already registered" . self::by(self::$byName[$name]) . '.');
		}
		if ($value < 1 || isset(self::$byValue[$value])) {
			$taken = isset(self::$byValue[$value]) ? ' by ' . self::$byValue[$value]->name . self::by(self::$byValue[$value]) : '';
			throw new InvalidArgumentException("Rarity value $value for '$name' is invalid or already used$taken.");
		}

		$config = self::$overrides[$name] ?? [];
		$rarity = new self(
			$name,
			$value,
			(string) ($config['display_name'] ?? $label),
			(string) ($config['display_color'] ?? $color),
			(int) ($config['weight'] ?? $weight),
			(bool) ($config['enabled'] ?? true),
			$owner,
		);

		self::$byName[$name] = $rarity;
		self::$byValue[$value] = $rarity;
		return $rarity;
	}

	public static function get(string $name): ?self
	{
		return self::$byName[strtoupper($name)] ?? null;
	}

	public static function fromName(string $name): self
	{
		$rarity = self::get($name);
		return $rarity !== null && $rarity->enabled ? $rarity : self::fromValue(self::FALLBACK);
	}

	public static function fromValue(int $value): self
	{
		return self::$byValue[$value] ?? self::$byValue[self::FALLBACK];
	}

	/** @return list<self> */
	public static function cases(): array
	{
		return array_values(self::$byName);
	}

	/** @return list<self> */
	public static function enabled(): array
	{
		return array_values(array_filter(self::$byName, static fn(self $r): bool => $r->enabled));
	}

	public function label(): string
	{
		return $this->label;
	}

	public function color(): string
	{
		return $this->color;
	}

	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	private static function by(self $rarity): string
	{
		return $rarity->owner !== null ? ' by ' . $rarity->owner->getName() : '';
	}
}