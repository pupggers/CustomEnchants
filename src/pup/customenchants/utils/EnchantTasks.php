<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

final class EnchantTasks
{
    private static ?PluginBase $plugin = null;

    public static function init(PluginBase $plugin): void
    {
        self::$plugin = $plugin;
    }

    public static function later(int $ticks, \Closure $task): void
    {
        self::$plugin?->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($task): void {
            $task();
        }), max(1, $ticks));
    }

    public static function repeatTimes(int $period, int $times, \Closure $task): void
    {
        if (self::$plugin === null) {
            return;
        }
        $handler = null;
        $count = 0;
        $handler = self::$plugin->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function () use (&$handler, &$count, $times, $task): void {
                $count++;
                if ($task($count) === false || $count >= $times) {
                    $handler?->cancel();
                }
            }),
            max(1, $period)
        );
    }
}
