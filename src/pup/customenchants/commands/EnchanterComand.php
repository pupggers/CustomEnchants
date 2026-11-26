<?php

declare(strict_types=1);

namespace pup\customenchants\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pup\customenchants\commands\sub\AddEnchantSub;
use pup\customenchants\commands\sub\AddonSub;
use pup\customenchants\commands\sub\RemoveEnchantSub;
use pup\customenchants\commands\sub\TinkererSub;
use pup\customenchants\Main;
use pup\customenchants\menus\EnchanterMenu;
use pocketmine\player\Player;

final class EnchanterComand extends BaseCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "enchanter", "Manage custom enchantments", ["ce"]);
        $this->setPermission("enchant.command");
    }

    protected function prepare(): void
    {
        $this->registerSubCommand(new AddEnchantSub($this->main));
        $this->registerSubCommand(new RemoveEnchantSub($this->main));
        $this->registerSubCommand(new AddonSub($this->main));
        $this->registerSubCommand(new TinkererSub($this->main));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            EnchanterMenu::open($sender);
            return;
        }
        $this->sendUsage();
    }
}
