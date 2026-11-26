<?php

namespace pup\customenchants\items;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\VanillaEnchantments;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use pup\customenchants\items\interface\ItemInteractInterface;
use pup\customenchants\items\types\EnchantBook;
use pup\customenchants\items\types\EnchantmentBook;
use pup\customenchants\Main;
use pup\customenchants\utils\Rarity;
use Random\RandomException;
use Throwable;

final class ItemListener implements Listener
{
    public function onTransaction(InventoryTransactionEvent $event)
    : void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        $actions = array_values($transaction->getActions());

        if (count($actions) !== 2) {
            return;
        }

        try {
            foreach ($actions as $i => $action) {
                if (!$action instanceof SlotChangeAction) {
                    continue;
                }

                $otherAction = $actions[($i + 1) % 2];
                if (!$otherAction instanceof SlotChangeAction) {
                    continue;
                }

                $itemClicked = $action->getSourceItem();
                $itemClickedWith = $otherAction->getSourceItem();

                if ($itemClickedWith->getNamedTag()->getTag("customItem") === null) {
                    continue;
                }

                $customItemId = $itemClickedWith->getNamedTag()->getString("customItem");

                if ($customItemId === "enchantment_book") {
                    $enchantName = $itemClickedWith->getNamedTag()->getString("enchant", "");
                    $enchantLevel = $itemClickedWith->getNamedTag()->getInt("level", 1);

                    $customItem = new EnchantmentBook(
                        Main::getInstance()->getEnchantManager()->getByKey($enchantName) ?? VanillaEnchantments::PROTECTION(),
                        $enchantLevel
                    );
                } else {
                    $customItem = ItemRegistry::fromId($customItemId);
                }

                if ($customItem instanceof InventoryTransactionInterface) {
                    $customItem->onInventoryTransaction(
                        $event,
                        $player,
                        $itemClicked,
                        $itemClickedWith,
                        $action,
                        $otherAction
                    );
                }
            }
        } catch (Throwable $e) {
            Main::getInstance()->getMessages()->send($player, 'item_processing_error');
        }
    }

    /**
     * @throws RandomException
     */
    public function onClick(PlayerInteractEvent $event)
    : void
    {
        $player = $event->getPlayer();
        $itemClicked = $event->getItem();

        if ($itemClicked->getNamedTag()->getTag("customItem") === null) {
            return;
        }
        if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {

            $customItemId = $itemClicked->getNamedTag()->getString("customItem");
            if ($customItemId === "random_enchant_book") {
                $rarity = $itemClicked->getNamedTag()->getInt("rarity", Rarity::COMMON);
                $customItem = new EnchantBook($rarity);
                $customItem->onInteract($itemClicked, $player);
            } elseif ($customItemId === "magic_dust") {
                $magicDust = ItemRegistry::getAll()["magic_dust"] ?? null;
                if ($magicDust instanceof ItemInteractInterface) {
                    $magicDust->onInteract($itemClicked, $player);
                }
            }
        }
    }
}
