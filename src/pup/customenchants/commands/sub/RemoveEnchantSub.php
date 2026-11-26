<?php

declare(strict_types=1);

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pup\customenchants\EnchantManager;
use pup\customenchants\Main;
use pup\customenchants\MessageService;

final class RemoveEnchantSub extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "remove", "Remove an enchantment from the item in hand");
        $this->setPermission("enchant.command.remove");
    }

    /** @throws ArgumentOrderException */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('enchant'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;

        $manager = $this->main->getEnchantManager();
        $key = EnchantManager::normalizeKey((string) $args['enchant']);

        $enchant = $manager->getByKey($key);
        if ($enchant === null) {
            MessageService::send($sender, 'invalid_enchantment');
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        if ($item->isNull()) {
            MessageService::send($sender, 'item_null');
            return;
        }

        $name = $enchant->getName();
        $name = $name instanceof Translatable ? $name->getText() : $name;

        if (!$item->hasEnchantment($enchant)) {
            MessageService::send($sender, 'enchant_not_on_item', ['enchant_name' => $name]);
            return;
        }

        $item->removeEnchantment($enchant);
        $sender->getInventory()->setItemInHand($manager->rebuildLore($item));
        MessageService::send($sender, 'enchant_removed', ['enchant_name' => $name]);
    }
}
