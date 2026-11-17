<?php


namespace pup\customenchants\types;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\CustomEnchant;
use pup\customenchants\utils\EnchantScan;

abstract class RandomArmorEnchant extends CustomEnchant
{
    public static function onDamage(EntityDamageEvent $event)
    : void
    {
        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }
        $damager = null;
        if ($event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player) {
            $damager = $event->getDamager();
        }
		foreach ($player->getArmorInventory()->getContents() as $armor) {
			if (!$armor instanceof Armor) {
				continue;
			}
			foreach (EnchantScan::of($armor, static fn(object $e): bool => $e instanceof self) as [$enchant, $level]) {
				$enchant->execute($player, $damager, $armor, $level);
			}
		}
    }

    abstract public function execute(Player $player, ?Player $damager, Armor $armor, int $level)
    : void;

}