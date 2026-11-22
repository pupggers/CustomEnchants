<?php

declare(strict_types=1);

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pup\customenchants\Main;
use pup\customenchants\MessageService;

final class AddEnchantSub extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "add", "Add an enchantment to the item in hand");
        $this->setPermission("enchant.command.add");
    }

    /** @throws ArgumentOrderException */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('enchant'));
        $this->registerArgument(1, new IntegerArgument('level', true));
        $this->registerArgument(2, new BooleanArgument('force', true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;

        $item = $sender->getInventory()->getItemInHand();
        if ($item->isNull()) {
            MessageService::send($sender, 'item_null');
            return;
        }

        $manager = $this->main->getEnchantManager();
        $key = (string) $args['enchant'];
        $level = max(1, abs((int) ($args['level'] ?? 1)));
        $force = (bool) ($args['force'] ?? false);

        $result = $manager->apply($item, $key, $level, $force);
        if (!$result->isOk() && !($force && $result->status->isBypassable())) {
            $manager->sendResult($sender, $result, $key, $level);
            return;
        }

        $sender->getInventory()->setItemInHand($item);
        if (!$result->isOk()) {
            MessageService::send($sender, 'enchant_forced', ['enchant_name' => $manager->displayName($key)]);
        }
        MessageService::send($sender, 'enchant_applied', [
            'enchant_name' => $manager->displayName($key),
            'level' => $level,
        ]);
    }
}
