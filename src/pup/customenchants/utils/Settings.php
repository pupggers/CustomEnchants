<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\utils\Config;

final class Settings
{
	private static bool $booksEnabled = true;
	private static int $bookSuccessMin = 30;
	private static int $bookSuccessMax = 100;
	private static int $bookDestroyChance = 50;

	private static bool $blackScrollRemovesEnchants = true;

	private static string $loreEntryFormat = '{reset} {dark_gray}» {reset}{color}{name} {level}';
	private static string $loreHeader = '{reset}{light_purple}Enchantments:';
	private static bool $loreRomanNumerals = true;

	public static function load(Config $config): void
	{
		self::$booksEnabled = (bool) $config->get('book_chance', true);

		$min = self::clamp((int) $config->getNested('enchant_books.success_min', 30), 1, 100);
		self::$bookSuccessMin = $min;
		self::$bookSuccessMax = self::clamp((int) $config->getNested('enchant_books.success_max', 100), $min, 100);
		self::$bookDestroyChance = self::clamp((int) $config->getNested('enchant_books.destroy_chance', 50));

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

	public static function bookDestroyChance(): int
	{
		return self::$bookDestroyChance;
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
}
