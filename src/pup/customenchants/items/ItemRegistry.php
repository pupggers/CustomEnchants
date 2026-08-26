<?php

namespace pup\customenchants\items;

use InvalidArgumentException;
use pup\customenchants\Main;
use pup\customenchants\items\types\BlackScroll;
use pup\customenchants\items\types\WhiteScroll;

final class ItemRegistry
{
    /** @var array<string, CustomItem> */
    private static array $items = [];
    /** @var array<string, array<string, mixed>> */
    private static array $definitions = [];

    public static function init(): void
    {
        $path = Main::getInstance()->getDataFolder() . 'items.json';
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Could not read items.json: it must contain a JSON object.');
        }
        self::$definitions = $data;
        self::$items = [];

        if (self::isEnabled('white_scroll')) {
            self::register(new WhiteScroll());
        }
        if (self::isEnabled('black_scroll')) {
            self::register(new BlackScroll(self::getChance('black_scroll', 50)));
        }
    }

    public static function isEnabled(string $id): bool
    {
        return (self::$definitions[$id]['enabled'] ?? true) === true;
    }

    /** @param array<string, scalar> $replacements */
    public static function getName(string $id, string $default, array $replacements = []): string
    {
        $template = self::$definitions[$id]['display_name'] ?? $default;
        return Main::getInstance()->getMessages()->format((string) $template, null, $replacements);
    }

    /** @param string[] $default @param array<string, scalar> $replacements @return string[] */
    public static function getLore(string $id, array $default, array $replacements = []): array
    {
        $lore = self::$definitions[$id]['lore'] ?? $default;
        if (!is_array($lore)) {
            return $default;
        }
        return array_map(fn(mixed $line): string => Main::getInstance()->getMessages()->format((string) $line, null, $replacements), $lore);
    }

    public static function getChance(string $id, int $default): int
    {
        return max(1, min(100, (int) (self::$definitions[$id]['chance'] ?? $default)));
    }

    public static function register(CustomItem $item): void
    {
        self::$items[$item->getId()] = $item;
    }

    public static function fromId(string $id): CustomItem
    {
        if (!isset(self::$items[$id])) {
            throw new InvalidArgumentException("Custom item with ID {$id} is disabled or not registered");
        }
        return self::$items[$id];
    }

    /** @return array<string, CustomItem> */
    public static function getAll(): array
    {
        return self::$items;
    }
}
