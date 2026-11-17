<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\helmet;

use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\ToggledArmorEnchant;
use pup\customenchants\utils\EnchantTasks;

class ImplantsEnchant extends ToggledArmorEnchant
{
    /** @var array<string, true> */
    private static array $running = [];

    public function onEquip(Player $player, Item $item, int $level): void
    {
        $id = $player->getXuid();
        if (isset(self::$running[$id])) {
            return;
        }
        self::$running[$id] = true;
        EnchantTasks::repeatTimes(100, PHP_INT_MAX, function () use ($player, $id, $level): bool {
            $level = $player->getArmorInventory()->getHelmet()->getEnchantment($this)?->getLevel();
            if (!$player->isConnected() || $level === null) {
                unset(self::$running[$id]);
                return false;
            }
            if (random_int(1, 100) <= $this->getChance($level, $this->getMaxLevel())) {
                $player->getHungerManager()->addFood(1.0);
            }
            return true;
        });
    }

    public function onDequip(Player $player, Item $item, int $level): void
    {
		$id = $player->getXuid();
		unset(self::$running[$id]);
    }
}
