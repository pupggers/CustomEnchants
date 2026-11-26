<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\item\Item;
use WeakMap;

final class ArrowEnchants
{
	//TY KYRO
    /** @var WeakMap<Entity, Item>|null */
    private static ?WeakMap $bows = null;

    public static function onShoot(EntityShootBowEvent $event): void
    {
        if ($event->isCancelled()) {
            return;
        }
        self::capture($event->getProjectile(), $event->getBow());
    }

    public static function capture(Entity $projectile, Item $bow): void
    {
        self::map()[$projectile] = clone $bow;
    }

    public static function bowOf(Entity $projectile): ?Item
    {
        return self::map()[$projectile] ?? null;
    }

    /** @return WeakMap<Entity, Item> */
    private static function map(): WeakMap
    {
        return self::$bows ??= new WeakMap();
    }
}
