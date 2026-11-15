<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchantInterface;
use pup\customenchants\EnchantManager;
use pup\placeholderapi\placeholder\ClosurePlaceholder;
use pup\placeholderapi\PlaceholderApi;

final class EnchantPlaceholders
{
	public static function register(EnchantManager $enchantManager): void
	{
		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'enchant_name',
			function (?Player $player, ?string $params) use ($enchantManager): string {
				$enchant = $params !== null ? $enchantManager->getByKey($params) : null;
				if ($enchant === null) {
					return '';
				}
				$name = $enchant->getName();
				return $name instanceof Translatable ? $name->getText() : $name;
			},
			supportsParameters: true
		));

		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'enchant_max_level',
			function (?Player $player, ?string $params) use ($enchantManager): string {
				$enchant = $params !== null ? $enchantManager->getByKey($params) : null;
				return (string)$enchant?->getMaxLevel();
			},
			supportsParameters: true
		));

		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'enchant_rarity',
			function (?Player $player, ?string $params) use ($enchantManager): string {
				$enchant = $params !== null ? $enchantManager->getByKey($params) : null;
				return $enchant === null ? '' : Rarity::fromValue($enchant->getRarity())->label();
			},
			supportsParameters: true
		));

		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'enchant_rarity_color',
			function (?Player $player, ?string $params) use ($enchantManager): string {
				$enchant = $params !== null ? $enchantManager->getByKey($params) : null;
				return $enchant === null ? '' : Rarity::fromValue($enchant->getRarity())->color();
			},
			supportsParameters: true
		));

		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'enchant_chance',
			function (?Player $player, ?string $params) use ($enchantManager): string {
				if ($params === null || !str_contains($params, ',')) {
					return '';
				}
				[$key, $levelStr] = array_map('trim', explode(',', $params, 2));
				$enchant = $enchantManager->getByKey($key);
				if (!$enchant instanceof CustomEnchantInterface || !ctype_digit($levelStr)) {
					return '';
				}
				return number_format($enchant->getChance((int) $levelStr, $enchant->getMaxLevel()), 2);
			},
			supportsParameters: true
		));

		PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder(
			'roman',
			fn(?Player $player, ?string $params): string =>
			$params !== null && ctype_digit($params) ? EnchantManager::intToRoman((int) $params) : ($params ?? ''),
			supportsParameters: true
		));
	}
}