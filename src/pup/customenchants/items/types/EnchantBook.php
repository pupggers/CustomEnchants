<?php

namespace pup\customenchants\items\types;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\items\interface\ItemInteractInterface;
use pup\customenchants\utils\Rarity;
use pup\customenchants\Main;
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

        $color = Rarity::getColor($rarity);
        $rarityName = Rarity::getName($rarity);

        $replacements = ['rarity_color' => $color, 'rarity_name' => $rarityName];
        parent::__construct('random_enchant_book', ItemRegistry::getName('random_enchant_book', 'Enchantment Book ({rarity_name})', $replacements), ItemRegistry::getLore('random_enchant_book', ['Right-click to use'], $replacements), $item);
    }

    /**
     * @throws RandomException
     */
    public function onInteract(Item $item, Player $player): void
    {
        $config = Main::getInstance()->getConfig();
        $chanceEnabled = $config->get("book_chance", true);
        $baseChance = random_int(1, 100);

        $enchantments = $this->getEnchantmentsByRarity($this->rarity);

        if (empty($enchantments)) {
            Main::getInstance()->getMessages()->send($player, 'no_enchants_for_rarity');
            return;
        }

        $amountToProcess = 1;
        if ($player->isSneaking()) {
            $amountToProcess = $item->getCount();
        }

        $successCount = 0;
        $itemsToGive = [];

        for ($i = 0; $i < $amountToProcess; $i++) {
            $randomEnchant = $enchantments[array_rand($enchantments)];

            $enchantmentBook = new EnchantmentBook(
                $randomEnchant,
                1);

            $itemsToGive[] = $enchantmentBook->getItem();
        }

        $result = $player->getInventory()->addItem(...$itemsToGive);

        $successCount = count($itemsToGive) - count($result);

        if ($successCount > 0) {
            $item->setCount($item->getCount() - $successCount);
            $player->getInventory()->setItemInHand($item->getCount() > 0 ? $item : VanillaItems::AIR());

            if ($successCount > 1) {
                Main::getInstance()->getMessages()->send($player, 'books_created', ['amount' => $successCount]);
            }
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
