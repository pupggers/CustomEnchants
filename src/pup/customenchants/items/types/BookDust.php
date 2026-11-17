<?php

namespace pup\customenchants\items\types;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\AnvilUseSound;
use pup\customenchants\items\CustomItem;
use pup\customenchants\items\interface\InventoryTransactionInterface;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\Main;
use pup\customenchants\utils\Settings;
use Random\RandomException;

final class BookDust extends CustomItem implements InventoryTransactionInterface
{
    public function __construct(private readonly DustType $type)
    {
        $item = ItemRegistry::getMaterial($type->value, $type->defaultMaterial());
        $item->getNamedTag()->setString(self::CUSTOM_ITEM_ID, $type->value);

        $replacements = ['amount' => ItemRegistry::getAmount($type->value, $type->defaultAmount())];

        parent::__construct(
            $type->value,
            ItemRegistry::getName($type->value, $type->defaultName(), $replacements),
            ItemRegistry::getLore($type->value, $type->defaultLore(), $replacements),
            $item
        );
    }

	/**
	 * @throws RandomException
	 */
	public function onInventoryTransaction(
        InventoryTransactionEvent $e,
        Player                    $player,
        Item                      $item,
        Item                      $target,
        SlotChangeAction          $action1,
        SlotChangeAction          $action2
    )
    : void
    {
        $dust = null;
        $targetItem = null;
        $dustAction = null;
        $targetAction = null;

        if ($this->isThisDust($item)) {
            $dust = $item;
            $targetItem = $target;
            $dustAction = $action1;
            $targetAction = $action2;
        } elseif ($this->isThisDust($target)) {
            $dust = $target;
            $targetItem = $item;
            $dustAction = $action2;
            $targetAction = $action1;
        }

        if ($dust === null || $targetItem === null || $targetItem->isNull()) {
            return;
        }

        if (!EnchantmentBook::isBook($targetItem)
            && $targetItem->getNamedTag()->getTag(self::CUSTOM_ITEM_ID) !== null) {
            return;
        }

        $messages = Main::getInstance()->getMessages();
        $world = $player->getWorld();
        $tag = $targetItem->getNamedTag();

        if (!EnchantmentBook::isBook($targetItem) || $tag->getTag('success') === null) {
            $messages->send($player, 'dust_books_only');
            $world->addSound($player->getLocation(), new AnvilFallSound());
            $e->cancel();
            return;
        }

        $success = $tag->getInt('success');
        $destroy = $tag->getInt('destroy', Settings::bookDestroyChance());
        $amount = ItemRegistry::getAmount($this->type->value, $this->type->defaultAmount());

        $newSuccess = $success;
        $newDestroy = $destroy;

        switch ($this->type) {
            case DustType::GLOWSTONE:
                if ($success >= 100) {
                    $messages->send($player, 'dust_maxed');
                    $world->addSound($player->getLocation(), new AnvilFallSound());
                    $e->cancel();
                    return;
                }
                $newSuccess = Settings::clamp($success + $amount);
                break;
            case DustType::GUNPOWDER:
                if ($destroy <= 0) {
                    $messages->send($player, 'dust_maxed');
                    $world->addSound($player->getLocation(), new AnvilFallSound());
                    $e->cancel();
                    return;
                }
                $newDestroy = Settings::clamp($destroy - $amount);
                break;
            case DustType::BIO:
                break;
        }

        if ($this->type !== DustType::BIO) {
            $enchant = Main::getInstance()->getEnchantManager()->getByKey($tag->getString('enchant', ''));
            if ($enchant === null) {
                $messages->send($player, 'invalid_enchantment');
                $e->cancel();
                return;
            }
            $rebuilt = new EnchantmentBook($enchant, $tag->getInt('level', 1), $newSuccess, $newDestroy);
            $targetAction->getInventory()->setItem($targetAction->getSlot(), $rebuilt->getItem());
        }

        $dust->pop();
        $dustAction->getInventory()->setItem($dustAction->getSlot(), $dust->getCount() > 0 ? $dust : VanillaItems::AIR());

        match ($this->type) {
            DustType::GLOWSTONE => $messages->send($player, 'dust_success_boosted', ['success' => $newSuccess]),
            DustType::GUNPOWDER => $messages->send($player, 'dust_destroy_reduced', ['destroy' => $newDestroy]),
            DustType::BIO => $messages->send($player, 'dust_nothing'),
        };
        $world->addSound($player->getLocation(), new AnvilUseSound());
        $e->cancel();
    }

    private function isThisDust(Item $item)
    : bool
    {
        return $item->getNamedTag()->getString(self::CUSTOM_ITEM_ID, '') === $this->type->value;
    }
}
