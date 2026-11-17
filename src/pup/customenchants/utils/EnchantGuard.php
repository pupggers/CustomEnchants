<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use Closure;

final class EnchantGuard
{
    /** @var array<string, true> */
    private static array $active = [];

	/**
	 * @param string $key
	 * @param Closure $fn
	 * @return bool
	 */
    public static function run(string $key, Closure $fn): bool
    {
        if (isset(self::$active[$key])) {
            return false;
        }
        self::$active[$key] = true;
        try {
            $fn();
        } finally {
            unset(self::$active[$key]);
        }
        return true;
    }

    public static function active(string $key): bool
    {
        return isset(self::$active[$key]);
    }
}
