<?php

namespace pup\customenchants\items\types;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\interface\ItemInteractInterface;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\Main;
use pup\customenchants\utils\Rarity;
use Random\RandomException;

final class EnchantBook extends CustomItem implements ItemInteractInterface
{
    private int $rarity;

    public function __construct(int $rarity)
    {
        $this->rarity = $rarity;
        $item = VanillaItems::BOOK();

        $item->getNamedTag()
            ->setString(self::CUSTOM_ITEM_ID, "random_enchant_book")
            ->setInt("rarity", $rarity);

        $rarityEnum = Rarity::fromValue($rarity);
        $color = $rarityEnum->color();
        $rarityName = $rarityEnum->label();

        $replacements = ['rarity_color' => $color, 'rarity_name' => $rarityName];
        parent::__construct('random_enchant_book', ItemRegistry::getName('random_enchant_book', 'Enchantment Book ({rarity_name})', $replacements), ItemRegistry::getLore('random_enchant_book', ['Right-click to use'], $replacements), $item);
    }

    /**
     * @throws RandomException
     */
    public function onInteract(Item $item, Player $player): void
    {
        $enchantments = $this->getEnchantmentsByRarity($this->rarity);

        if (empty($enchantments)) {
            Main::getInstance()->getMessages()->send($player, 'no_enchants_for_rarity');
            return;
        }

        $amountToProcess = 1;
        if ($player->isSneaking()) {
            $amountToProcess = $item->getCount();
        }

        $itemsToGive = [];

        for ($i = 0; $i < $amountToProcess; $i++) {
            $randomEnchant = $enchantments[array_rand($enchantments)];

            $enchantmentBook = new EnchantmentBook(
                $randomEnchant,
                1);

            $itemsToGive[] = $enchantmentBook->getItem();
        }

        $result = $player->getInventory()->addItem(...$itemsToGive);

        $item->setCount($item->getCount() - $amountToProcess);
        $player->getInventory()->setItemInHand($item->getCount() > 0 ? $item : VanillaItems::AIR());

        if ($amountToProcess > 1) {
            Main::getInstance()->getMessages()->send($player, 'books_created', ['amount' => $amountToProcess]);
        }

        if (!empty($result)) {
            Main::getInstance()->getMessages()->send($player, 'inventory_full_books', ['amount' => count($result)]);

            foreach ($result as $remainingItem) {
                $player->getWorld()->dropItem($player->getPosition(), $remainingItem);
            }
        }
    }

    private function getEnchantmentsByRarity(int $rarity): array
    {
        $enchantments = [];
        foreach (Main::getInstance()->getEnchantManager()->getEnabled() as $enchant) {
            if ($enchant !== null && $enchant->getRarity() === $rarity) {
                $enchantments[] = $enchant;
            }
        }

        return $enchantments;
    }
}
