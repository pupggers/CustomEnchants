<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\helmet;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;
use pup\customenchants\utils\EnchantTasks;

class ImplantsEnchant extends ToggledArmorEnchant
{
    /** @var array<int, true> keyed by spl_object_id, so a rejoin gets a fresh timer */
    private static array $running = [];

    public function onEquip(Player $player, Item $item, int $level, int $slot): void
    {
        $id = spl_object_id($player);
        if (isset(self::$running[$id])) {
            return;
        }
        self::$running[$id] = true;
        EnchantTasks::repeatTimes(100, PHP_INT_MAX, function () use ($player, $id): bool {
            $level = $player->isConnected() && !$player->isClosed()
                ? $player->getArmorInventory()->getHelmet()->getEnchantment($this)?->getLevel()
                : null;
            if ($level === null) {
                unset(self::$running[$id]);
                return false;
            }
            if ($player->isAlive() && random_int(1, 100) <= $this->getChance($level, $this->getMaxLevel())) {
                $player->getHungerManager()->addFood(1.0);
            }
            return true;
        });
    }

    public function onDequip(Player $player, Item $item, int $level, int $slot): void
    {
    }
}
