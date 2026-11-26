<?php

declare(strict_types=1);

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pup\customenchants\Main;
use pup\customenchants\menus\TinkererMenu;

final class TinkererSub extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "tinkerer", "Trade enchantment books for Magic Dust");
        $this->setPermission("enchant.command.tinkerer");
    }

    protected function prepare(): void
    {
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            TinkererMenu::open($sender);
        }
    }
}
