<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\utils\Config;

final class Settings
{
	private static bool $booksEnabled = true;
	private static int $bookSuccessMin = 30;
	private static int $bookSuccessMax = 100;

	/** Level a rarity book gives: "random", a number, or "min-max". Unlisted rarities use "default". */
	private const array DEFAULT_BOOK_LEVELS = [
		'default' => 1,
		'COMMON' => 'random',
		'UNCOMMON' => 'random',
		'RARE' => 'random',
		'EPIC' => 'random',
		'LEGENDARY' => 'random',
	];

	private const array DEFAULT_ITEM_TYPE_NAMES = [
		'all' => 'Any item', 'armor' => 'Armor', 'helmet' => 'Helmets', 'chestplate' => 'Chestplates',
		'leggings' => 'Leggings', 'boots' => 'Boots', 'shield' => 'Shields', 'sword' => 'Swords',
		'trident' => 'Tridents', 'bow' => 'Bows', 'crossbow' => 'Crossbows', 'shears' => 'Shears',
		'flint_and_steel' => 'Flint and Steel', 'block_tools' => 'Tools', 'axe' => 'Axes', 'pickaxe' => 'Pickaxes',
		'shovel' => 'Shovels', 'hoe' => 'Hoes', 'fishing_rod' => 'Fishing Rods', 'carrot_on_stick' => 'Carrot on a Stick',
		'compass' => 'Compasses', 'mask' => 'Masks', 'elytra' => 'Elytra', 'brush' => 'Brushes', 'weapons' => 'Weapons',
	];

	/** @var array<string, mixed> */
	private static array $bookLevels = self::DEFAULT_BOOK_LEVELS;

	/** @var array<string, string> */
	private static array $itemTypeNames = self::DEFAULT_ITEM_TYPE_NAMES;

	private static bool $blackScrollRemovesEnchants = true;

	private static string $loreEntryFormat = '{reset} {dark_gray}» {reset}{color}{name} {level}';
	private static string $loreHeader = '{reset}{light_purple}Enchantments:';
	private static bool $loreRomanNumerals = true;

	private static float $maxLevelChance = 70.0;

	public static function load(Config $config): void
	{
		self::$booksEnabled = (bool) $config->get('book_chance', true);

		$min = self::clamp((int) $config->getNested('enchant_books.success_min', 30), 1, 100);
		self::$bookSuccessMin = $min;
		self::$bookSuccessMax = self::clamp((int) $config->getNested('enchant_books.success_max', 100), $min, 100);
		$levels = $config->getNested('enchant_books.levels', []);
		self::$bookLevels = (is_array($levels) ? array_change_key_case($levels, CASE_UPPER) : []) + self::DEFAULT_BOOK_LEVELS;
		if (isset(self::$bookLevels['DEFAULT'])) {
			self::$bookLevels['default'] = self::$bookLevels['DEFAULT'];
		}
		$names = $config->getNested('enchant_books.item_type_names', []);
		self::$itemTypeNames = array_filter(is_array($names) ? $names : [], 'is_string') + self::DEFAULT_ITEM_TYPE_NAMES;

		self::$blackScrollRemovesEnchants = $config->getNested('black_scroll.remove_enchantments', true) === true;

		self::$loreEntryFormat = (string) $config->getNested(
			'enchant_lore.entry_format',
			'{reset} {dark_gray}» {reset}{color}{name} {level}'
		);
		self::$loreHeader = (string) $config->getNested(
			'enchant_lore.header',
			'{reset}{light_purple}Enchantments:'
		);
		self::$loreRomanNumerals = (bool) $config->getNested('enchant_lore.roman_numerals', true);
		self::$maxLevelChance = (float) $config->get('max_level_chance', 70);
	}

	public static function clamp(int $value, int $min = 0, int $max = 100): int
	{
		return max($min, min($max, $value));
	}

	public static function booksEnabled(): bool
	{
		return self::$booksEnabled;
	}

	public static function bookSuccessMin(): int
	{
		return self::$bookSuccessMin;
	}

	public static function bookSuccessMax(): int
	{
		return self::$bookSuccessMax;
	}

	/** A book's success and destroy rates always add up to 100. */
	public static function bookDestroyFor(int $success): int
	{
		return 100 - self::clamp($success);
	}

	public static function randomBookSuccess(): int
	{
		return mt_rand(self::$bookSuccessMin, self::$bookSuccessMax);
	}

	/** The level a rarity book of $rarityName rolls for an enchant whose max level is $maxLevel. */
	public static function bookLevel(string $rarityName, int $maxLevel): int
	{
		$maxLevel = max(1, $maxLevel);
		$setting = self::$bookLevels[strtoupper($rarityName)] ?? self::$bookLevels['default'] ?? 1;

		[$min, $max] = match (true) {
			is_int($setting) => [$setting, $setting],
			is_string($setting) && strtolower(trim($setting)) === 'random' => [1, $maxLevel],
			is_string($setting) && preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $setting, $m) === 1 => [(int) $m[1], (int) $m[2]],
			is_string($setting) && ctype_digit(trim($setting)) => [(int) $setting, (int) $setting],
			default => [1, 1],
		};
		$min = self::clamp($min, 1, $maxLevel);
		$max = self::clamp($max, $min, $maxLevel);
		return mt_rand($min, $max);
	}

	/** @param list<string> $tags */
	public static function itemTypeNames(array $tags): string
	{
		return implode(', ', array_unique(array_map(
			static fn(string $tag): string => self::$itemTypeNames[$tag] ?? ucwords(str_replace('_', ' ', $tag)),
			$tags
		)));
	}

	public static function blackScrollRemovesEnchants(): bool
	{
		return self::$blackScrollRemovesEnchants;
	}

	public static function loreEntryFormat(): string
	{
		return self::$loreEntryFormat;
	}

	public static function loreHeader(): string
	{
		return self::$loreHeader;
	}

	public static function loreRomanNumerals(): bool
	{
		return self::$loreRomanNumerals;
	}

	public static function maxLevelChance(): float
	{
		return self::$maxLevelChance;
	}
}
