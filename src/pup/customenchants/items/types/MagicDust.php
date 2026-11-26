<?php

namespace pup\customenchants\items\types;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\interface\ItemInteractInterface;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\Main;
use Random\RandomException;

final class MagicDust extends CustomItem implements ItemInteractInterface
{
    public const string ID = 'magic_dust';

    public function __construct()
    {
        $item = ItemRegistry::getMaterial(self::ID, VanillaItems::SUGAR());
        $item->getNamedTag()->setString(self::CUSTOM_ITEM_ID, self::ID);

        parent::__construct(
            self::ID,
            ItemRegistry::getName(self::ID, '{light_purple}Magic Dust'),
            ItemRegistry::getLore(self::ID, ['Right-click to use']),
            $item
        );
    }

    /**
     * @throws RandomException
     */
    public function onInteract(Item $item, Player $player)
    : void
    {
        $amount = $player->isSneaking() ? $item->getCount() : 1;

        $rewards = [];
        for ($i = 0; $i < $amount; $i++) {
            $type = $this->rollType();
            $dust = ItemRegistry::getAll()[$type->value] ?? null;
            if ($dust === null) {
                break;
            }
            $rewards[] = $dust->getItem();
        }
        if ($rewards === []) {
            return;
        }

        $item->setCount($item->getCount() - count($rewards));
        $player->getInventory()->setItemInHand($item->getCount() > 0 ? $item : VanillaItems::AIR());

        $leftover = $player->getInventory()->addItem(...$rewards);
        foreach ($leftover as $remaining) {
            $player->getWorld()->dropItem($player->getPosition(), $remaining);
        }

        if (count($rewards) === 1) {
            $name = $rewards[0]->getCustomName();
            Main::getInstance()->getMessages()->send($player, 'dust_opened', ['dust_name' => $name]);
        } else {
            Main::getInstance()->getMessages()->send($player, 'dusts_opened', ['amount' => count($rewards)]);
        }
        if ($leftover !== []) {
            Main::getInstance()->getMessages()->send($player, 'inventory_full_dust', ['amount' => count($leftover)]);
        }
    }

    /**
     * Weighted pick. If every weight is 0 the three dusts are equally likely.
     *
     * @throws RandomException
     */
    private function rollType()
    : DustType
    {
        $weights = [];
        foreach (DustType::cases() as $type) {
            $weights[$type->value] = ItemRegistry::getWeight(self::ID, $type->weightKey(), 1);
        }
        $total = array_sum($weights);
        if ($total <= 0) {
            return DustType::cases()[array_rand(DustType::cases())];
        }

        $roll = random_int(1, $total);
        foreach (DustType::cases() as $type) {
            $roll -= $weights[$type->value];
            if ($roll <= 0) {
                return $type;
            }
        }
        return DustType::BIO;
    }
}
