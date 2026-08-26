<?php

namespace pup\customenchants;

use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pup\customenchants\types\WeaponEnchant;
use pup\customenchants\utils\Rarity;
use pup\placeholderapi\PlaceholderApi;

final class EnchantPlaceholders
{
    public static function register(EnchantManager $enchantManager): void
    {
        PlaceholderApi::registerPlaceholder(
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
        );

        PlaceholderApi::registerPlaceholder(
            'enchant_max_level',
            function (?Player $player, ?string $params) use ($enchantManager): string {
                $enchant = $params !== null ? $enchantManager->getByKey($params) : null;
                return (string) $enchant?->getMaxLevel();
            },
            supportsParameters: true
        );

        PlaceholderApi::registerPlaceholder(
            'enchant_rarity',
            function (?Player $player, ?string $params) use ($enchantManager): string {
                $enchant = $params !== null ? $enchantManager->getByKey($params) : null;
                if (!$enchant instanceof CustomEnchant) {
                    return '';
                }
                return ucfirst(strtolower($enchant->getRarity()->name));
            },
            supportsParameters: true
        );

        PlaceholderApi::registerPlaceholder(
            'enchant_rarity_color',
            function (?Player $player, ?string $params) use ($enchantManager): string {
                $enchant = $params !== null ? $enchantManager->getByKey($params) : null;
                return $enchant instanceof CustomEnchant ? Rarity::getColor($enchant->getRarity()) : '';
            },
            supportsParameters: true
        );

        PlaceholderApi::registerPlaceholder(
            'enchant_chance',
            function (?Player $player, ?string $params) use ($enchantManager): string {
                if ($params === null || !str_contains($params, ',')) {
                    return '';
                }
                [$key, $levelStr] = explode(',', $params, 2);
                $enchant = $enchantManager->getByKey($key);
                if (!$enchant instanceof CustomEnchant || !ctype_digit($levelStr)) {
                    return '';
                }
                $level = (int) $levelStr;
                return number_format($enchant->getChance($level, $enchant->getMaxLevel()), 2);
            },
            supportsParameters: true
        );

        PlaceholderApi::registerPlaceholder(
            'roman',
            fn(?Player $player, ?string $params): string =>
            $params !== null && ctype_digit($params) ? EnchantManager::intToRoman((int) $params) : ($params ?? ''),
            supportsParameters: true
        );
    }

}