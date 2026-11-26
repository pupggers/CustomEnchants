<?php

namespace pup\customenchants\items\types;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

enum DustType: string
{
    case GLOWSTONE = 'glowstone_dust';
    case GUNPOWDER = 'gunpowder_dust';
    case BIO = 'bio_dust';

    public function weightKey()
    : string
    {
        return match ($this) {
            self::GLOWSTONE => 'glowstone',
            self::GUNPOWDER => 'gunpowder',
            self::BIO => 'bio',
        };
    }

    public function defaultName()
    : string
    {
        return match ($this) {
            self::GLOWSTONE => '{yellow}Glowstone Dust',
            self::GUNPOWDER => '{gray}Gunpowder Dust',
            self::BIO => '{green}Bio Product',
        };
    }

    /** @return string[] */
    public function defaultLore()
    : array
    {
        return match ($this) {
            self::GLOWSTONE => ['{gray}Increases an enchantment book\'s', '{gray}success rate by {green}{amount}%', '', '{dark_gray}Combine with an enchantment book'],
            self::GUNPOWDER => ['{gray}Decreases an enchantment book\'s', '{gray}destroy rate by {green}{amount}%', '', '{dark_gray}Combine with an enchantment book'],
            self::BIO => ['{gray}Does absolutely nothing.', '', '{dark_gray}Combine with an enchantment book'],
        };
    }

    public function defaultAmount()
    : int
    {
        return match ($this) {
            self::GLOWSTONE, self::GUNPOWDER => 10,
            self::BIO => 0,
        };
    }

    public function defaultMaterial()
    : Item
    {
        return match ($this) {
            self::GLOWSTONE => VanillaItems::GLOWSTONE_DUST(),
            self::GUNPOWDER => VanillaItems::GUNPOWDER(),
            self::BIO => VanillaItems::SLIMEBALL(),
        };
    }
}
