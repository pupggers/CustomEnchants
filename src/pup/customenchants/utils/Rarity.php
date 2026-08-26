<?php

namespace pup\customenchants\utils;

final class Rarity
{
    private static array $rarities = [];

    public const int COMMON = 1;
    public const int UNCOMMON = 2;
    public const int RARE = 3;
    public const int EPIC = 4;
    public const int LEGENDARY = 5;
    public const int MASTERY = 6;
    public const int HEROIC = 7;

    public static function init(array $config): void
    {
        self::$rarities = [
            self::COMMON => [
                'name' => 'COMMON',
                'color' => $config['COMMON']['display_color'] ?? '§f',
                'weight' => $config['COMMON']['weight'] ?? 50,
                'enabled' => $config['COMMON']['enabled'] ?? true
            ],
            self::UNCOMMON => [
                'name' => 'UNCOMMON',
                'color' => $config['UNCOMMON']['display_color'] ?? '§a',
                'weight' => $config['UNCOMMON']['weight'] ?? 25,
                'enabled' => $config['UNCOMMON']['enabled'] ?? true
            ],
            self::RARE => [
                'name' => 'RARE',
                'color' => $config['RARE']['display_color'] ?? '§9',
                'weight' => $config['RARE']['weight'] ?? 15,
                'enabled' => $config['RARE']['enabled'] ?? true
            ],
            self::EPIC => [
                'name' => 'EPIC',
                'color' => $config['EPIC']['display_color'] ?? '§5',
                'weight' => $config['EPIC']['weight'] ?? 10,
                'enabled' => $config['EPIC']['enabled'] ?? true
            ],
            self::LEGENDARY => [
                'name' => 'LEGENDARY',
                'color' => $config['LEGENDARY']['display_color'] ?? '§6',
                'weight' => $config['LEGENDARY']['weight'] ?? 5,
                'enabled' => $config['LEGENDARY']['enabled'] ?? true
            ],
            self::MASTERY => [
                'name' => 'MASTERY',
                'color' => $config['MASTERY']['display_color'] ?? '§3',
                'weight' => $config['MASTERY']['weight'] ?? 3,
                'enabled' => $config['MASTERY']['enabled'] ?? true
            ],
            self::HEROIC => [
                'name' => 'HEROIC',
                'color' => $config['HEROIC']['display_color'] ?? '§4',
                'weight' => $config['HEROIC']['weight'] ?? 0,
                'enabled' => $config['HEROIC']['enabled'] ?? true
            ]
        ];
    }

    public static function isEnabled(int $rarity): bool
    {
        return self::$rarities[$rarity]['enabled'] ?? false;
    }

    public static function getEnabledRarities(): array
    {
        return array_filter(self::$rarities, fn($r) => $r['enabled']);
    }

    public static function getColor(int $rarity): string
    {
        return self::$rarities[$rarity]['color'] ?? '§f';
    }

    public static function getName(int $rarity): string
    {
        return self::$rarities[$rarity]['name'] ?? 'UNKNOWN';
    }

    public static function fromName(string $name): int
    {
        foreach (self::$rarities as $id => $rarity) {
            if (strtoupper($name) === $rarity['name'] && self::isEnabled($id)) {
                return $id;
            }
        }
        return self::UNCOMMON;
    }
}
