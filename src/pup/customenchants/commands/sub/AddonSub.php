<?php

declare(strict_types=1);

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pup\customenchants\Main;
use pup\customenchants\MessageService;

final class AddonSub extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "addon", "Install, list or remove enchant addons from GitHub");
        $this->setPermission("enchant.command.addon");
    }

    /** @throws ArgumentOrderException */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('action'));
        $this->registerArgument(1, new RawStringArgument('name', true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $addons = $this->main->getAddonManager();
        $action = strtolower((string) $args['action']);
        $name = isset($args['name']) ? (string) $args['name'] : null;

        match (true) {
            $action === 'list' => $addons->list($sender),
            $action === 'install' && $name !== null => $addons->install($sender, $name),
            $action === 'remove' && $name !== null => $addons->remove($sender, $name),
            default => MessageService::send($sender, 'addon_usage'),
        };
    }
}
