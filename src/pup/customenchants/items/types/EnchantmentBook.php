<?php

namespace pup\customenchants\items\types;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\CustomEnchant;
use pup\customenchants\Main;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use pup\customenchants\utils\Rarity;

final class EnchantmentBook extends CustomItem implements InventoryTransactionInterface
{
    public function __construct(Enchantment $enchantment, int $level = 1){
        $item = VanillaItems::BOOK();

        $item->getNamedTag()
            ->setString(self::CUSTOM_ITEM_ID, "enchantment_book")
            ->setString("enchant", $enchantment->getName())
            ->setInt("level", $level)
            ->setString("uid", uniqid());

        $name = $enchantment->getName();

        $replacements = ['rarity_color' => Rarity::getColor($enchantment->getRarity()), 'rarity_name' => Rarity::getName($enchantment->getRarity()), 'enchant_name' => $name, 'level' => $level];
        parent::__construct('enchantment_book', ItemRegistry::getName('enchantment_book', '{enchant_name} {level}', $replacements), ItemRegistry::getLore('enchantment_book', ['Combine with an item to use'], $replacements), $item);
    }

    public function onInventoryTransaction(InventoryTransactionEvent $e, Player $player, Item $item, Item $target, SlotChangeAction $action1, SlotChangeAction $action2)
    : void
    {
        $book = null;
        $targetItem = null;
        $bookAction = null;
        $targetAction = null;

        if ($this->isEnchantBook($item)) {
            $book = $item;
            $targetItem = $target;
            $bookAction = $action1;
            $targetAction = $action2;
        } elseif ($this->isEnchantBook($target)) {
            $book = $target;
            $targetItem = $item;
            $bookAction = $action2;
            $targetAction = $action1;
        }

        if ($book === null || $targetItem === null || $targetItem->isNull()) {
            return;
        }

        $bookEnchantName = $book->getNamedTag()->getString("enchant");
        $bookLevel = $book->getNamedTag()->getInt("level");

        if ($this->isEnchantBook($targetItem)) {
            $targetEnchantName = $targetItem->getNamedTag()->getString("enchant");
            $targetLevel = $targetItem->getNamedTag()->getInt("level");

            if ($bookEnchantName === $targetEnchantName && $bookLevel === $targetLevel) {
                $manager = Main::getInstance()->getEnchantManager();
                $enchant = $manager->getByKey($bookEnchantName);
                $maxLevel = $enchant?->getMaxLevel() ?? 1;

                if ($bookLevel < $maxLevel) {
                    $newLevel = $bookLevel + 1;

                    if ($enchant === null) {
                        $e->cancel();
                        return;
                    }
                    $newBook = new self($enchant, $newLevel);

                    $book->pop();
                    $targetItem->pop();

                    $targetAction->getInventory()->setItem($targetAction->getSlot(), $newBook->getItem());
                    $bookAction->getInventory()->setItem($bookAction->getSlot(), VanillaItems::AIR());

                    $player->getWorld()->addSound($player->getLocation(), new AnvilUseSound());
                } else {
                    Main::getInstance()->getMessages()->send($player, 'book_max_level');
                    $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());
                }
            } else {
                Main::getInstance()->getMessages()->send($player, 'book_combine_mismatch');
                $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());
            }
            $e->cancel();
            return;
        }

        if (!$targetItem instanceof Durable) {
            Main::getInstance()->getMessages()->send($player, 'durable_items_only');
            $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());
            $e->cancel();
            return;
        }

        $manager = Main::getInstance()->getEnchantManager();
        if ($manager->canApply($bookEnchantName, $targetItem)) {
            $enchant = $manager->getByKey($bookEnchantName);
            if ($enchant !== null) {
                $existingEnchant = $targetItem->getEnchantment($enchant);
                if ($existingEnchant !== null) {
                    $maxLevel = $enchant->getMaxLevel();
                    if ($existingEnchant->getLevel() >= $bookLevel) {
                        Main::getInstance()->getMessages()->send($player, 'item_has_equal_or_higher');
                        $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());
                        $e->cancel();
                        return;
                    }
                    if ($existingEnchant->getLevel() >= $maxLevel) {
                        $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());
                        Main::getInstance()->getMessages()->send($player, 'item_enchant_max_level');
                        $e->cancel();
                        return;
                    }
                }

                $targetItem->addEnchantment(new EnchantmentInstance($enchant, $bookLevel));
                $targetItem = $manager->rebuildLore($targetItem);

                $book->pop();
                $bookAction->getInventory()->setItem($bookAction->getSlot(), $book->getCount() > 0 ? $book : VanillaItems::AIR());
                $targetAction->getInventory()->setItem($targetAction->getSlot(), $targetItem);

                $player->getWorld()->addSound($player->getLocation(), new AnvilUseSound());
                $e->cancel();
                return;
            }
        }

        Main::getInstance()->getMessages()->send($player, 'cannot_apply');
        $e->cancel();
    }

    private function isEnchantBook(Item $item): bool
    {
        return $item->getNamedTag()->getString(self::CUSTOM_ITEM_ID, "") === "enchantment_book";
    }
}
