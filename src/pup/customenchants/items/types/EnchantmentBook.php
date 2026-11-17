<?php

namespace pup\customenchants\items\types;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\Main;
use pup\customenchants\utils\Rarity;
use pup\customenchants\utils\Settings;
use Random\RandomException;

final class EnchantmentBook extends CustomItem implements InventoryTransactionInterface
{
	/**
	 * @param Enchantment $enchantment
	 * @param int $level
	 * @param int|null $success
	 * @param int|null $destroy
	 * @throws RandomException
	 */
    public function __construct(Enchantment $enchantment, int $level = 1, ?int $success = null, ?int $destroy = null){
        $item = VanillaItems::BOOK();

        $name = $enchantment->getName();
        $name = $name instanceof Translatable ? $name->getText() : $name;

        $item->getNamedTag()
            ->setString(self::CUSTOM_ITEM_ID, "enchantment_book")
            ->setString("enchant", $name)
            ->setInt("level", $level)
            ->setString("uid", uniqid());

        $rarity = Rarity::fromValue($enchantment->getRarity());
        $replacements = [
            'rarity_color' => $rarity->color(),
            'rarity_name' => $rarity->label(),
            'enchant_name' => $name,
            'level' => $level
        ];

        $chanceLines = [];
        if (Settings::booksEnabled()) {
            $success = Settings::clamp($success ?? mt_rand(Settings::bookSuccessMin(), Settings::bookSuccessMax()));
            $destroy = Settings::clamp($destroy ?? Settings::bookDestroyChance());

            $item->getNamedTag()
                ->setInt("success", $success)
                ->setInt("destroy", $destroy);

            $replacements['success'] = $success;
            $replacements['destroy'] = $destroy;
            $chanceLines = ItemRegistry::getLines(
                'enchantment_book',
                'chance_lore',
                ['{green}Success rate: {success}%', '{red}Destroy rate: {destroy}%'],
                $replacements
            );
        }

        parent::__construct(
            'enchantment_book',
            ItemRegistry::getName('enchantment_book', '{enchant_name} {level}', $replacements),
            [...ItemRegistry::getLore('enchantment_book', ['Combine with an item to use'], $replacements), ...$chanceLines],
            $item
        );
    }

    /**
     * @throws RandomException
     */
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

        if (!$this->isEnchantBook($targetItem) && $targetItem->getNamedTag()->getTag(self::CUSTOM_ITEM_ID) !== null) {
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

                $success = 100;
                $destroy = 0;
                if (Settings::booksEnabled() && $book->getNamedTag()->getTag("success") !== null) {
                    $success = $book->getNamedTag()->getInt("success");
                    $destroy = $book->getNamedTag()->getInt("destroy", Settings::bookDestroyChance());
                }

                $book->pop();
                $bookAction->getInventory()->setItem($bookAction->getSlot(), $book->getCount() > 0 ? $book : VanillaItems::AIR());

                if (random_int(1, 100) <= $success) {
                    $targetItem->addEnchantment(new EnchantmentInstance($enchant, $bookLevel));
                    $targetItem = $manager->rebuildLore($targetItem);
                    $targetAction->getInventory()->setItem($targetAction->getSlot(), $targetItem);

                    $player->getWorld()->addSound($player->getLocation(), new AnvilUseSound());
                } else {
                    $this->onFailure($player, $targetAction, $targetItem, $destroy);
                }

                $e->cancel();
                return;
            }
        }

        Main::getInstance()->getMessages()->send($player, 'cannot_apply');
        $e->cancel();
    }

    /**
     * The book failed. $destroy % of the time the item is destroyed, otherwise it is simply kept.
     * An item with a White/Holy Scroll on it survives a destroy roll, and the scroll is used up.
     *
     * @throws RandomException
     */
    private function onFailure(Player $player, SlotChangeAction $targetAction, Item $targetItem, int $destroy)
    : void
    {
        $messages = Main::getInstance()->getMessages();
        $player->getWorld()->addSound($player->getLocation(), new AnvilFallSound());

        if (random_int(1, 100) > $destroy) {
            $messages->send($player, 'book_failed_kept');
            return;
        }

		if (WhiteScroll::isProtectedItem($targetItem)) {
			if (WhiteScroll::isHolyItem($targetItem)) {
				$messages->send($player, 'book_failed_holy_protected');
			} else {
				WhiteScroll::removeProtection($targetItem);
				$messages->send($player, 'book_failed_protected');
			}
			$targetAction->getInventory()->setItem($targetAction->getSlot(), $targetItem);
			return;
		}

        $targetAction->getInventory()->setItem($targetAction->getSlot(), VanillaItems::AIR());
        $messages->send($player, 'book_failed_destroyed');
    }

    public static function isBook(Item $item)
    : bool
    {
        return $item->getNamedTag()->getString(self::CUSTOM_ITEM_ID, "") === "enchantment_book";
    }

    private function isEnchantBook(Item $item): bool
    {
        return self::isBook($item);
    }
}
