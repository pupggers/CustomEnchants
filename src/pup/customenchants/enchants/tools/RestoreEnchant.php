<?php


namespace pup\customenchants\enchants\tools;


use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\player\Player;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\EnchantManager;
use pup\customenchants\Main;
use pup\customenchants\types\ToolEnchant;

class RestoreEnchant extends ToolEnchant
{

    public function execute(Player $player, Item $item, Block $block, ?Event $event): void {
        if (!$item instanceof Tool) {
            return;
        }

        $maxDurability = $item->getMaxDurability();
        $currentDamage = $item->getDamage();

        if (!$item->isBroken() && $currentDamage < $maxDurability - 1) {
            return;
        }

        $newItem = clone $item;

        $enchantId = Main::getInstance()->getEnchantManager()->getByKey('restore');
        if ($enchantId !== null) {
            $newItem->removeEnchantment($enchantId);
        }
        //Interfering with drill, doesnt repair when using drill

        $restoredDurability = (int) ($maxDurability / 2);
        $newItem->setDamage($restoredDurability);

        $player->getInventory()->setItemInHand($newItem);

        Main::getInstance()->getMessages()->send($player, 'tool_repaired');
        $player->getWorld()->addParticle($player->getPosition(), new HappyVillagerParticle());
        $player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
    }
}
