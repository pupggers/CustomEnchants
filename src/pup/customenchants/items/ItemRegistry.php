<?php

namespace pup\customenchants\items;

use InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pup\customenchants\items\types\BlackScroll;
use pup\customenchants\items\types\BookDust;
use pup\customenchants\items\types\DustType;
use pup\customenchants\items\types\MagicDust;
use pup\customenchants\items\types\WhiteScroll;
use pup\customenchants\Main;
use pup\customenchants\utils\Settings;

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

		//Scrolls only worth when books and chance is enabled.

        if (self::isEnabled('white_scroll')) {
            self::register(new WhiteScroll());
			self::register(new WhiteScroll(true));
        }
        if (self::isEnabled('black_scroll')) {
            self::register(new BlackScroll(self::getChance('black_scroll', 50)));
        }

        if (Settings::booksEnabled()) {
            if (self::isEnabled(MagicDust::ID)) {
                self::register(new MagicDust());
            }
            foreach (DustType::cases() as $type) {
                if (self::isEnabled($type->value)) {
                    self::register(new BookDust($type));
                }
            }
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

    /**
     * @param string[] $default
     * @param array<string, scalar> $replacements
     * @return string[]
     */
    public static function getLines(string $id, string $key, array $default, array $replacements = []): array
    {
        $lines = self::$definitions[$id][$key] ?? $default;
        if (!is_array($lines)) {
            $lines = $default;
        }
        return array_map(fn(mixed $line): string => Main::getInstance()->getMessages()->format((string) $line, null, $replacements), $lines);
    }

    public static function getMaterial(string $id, Item $default): Item
    {
        $name = self::$definitions[$id]['item'] ?? null;
        if (is_string($name)) {
            $parsed = StringToItemParser::getInstance()->parse($name);
            if ($parsed !== null) {
                return $parsed->setCount(1);
            }
        }
        return $default;
    }

    public static function getAmount(string $id, int $default): int
    {
        return max(0, (int) (self::$definitions[$id]['amount'] ?? $default));
    }

    public static function getWeight(string $id, string $key, int $default): int
    {
        return max(0, (int) (self::$definitions[$id]['weights'][$key] ?? $default));
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
