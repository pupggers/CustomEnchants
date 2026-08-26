<?php

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pup\customenchants\Main;

final class RemoveEnchantSub extends BaseSubCommand
{

    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "remove", "Removes an enchantment");
        $this->setPermission("enchant.command.remove");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare()
    : void
    {
        $this->registerArgument(0, new RawStringArgument('enchant'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args)
    : void
    {
        if(!$sender instanceof Player) return;

        if(isset($args['enchant'])) {
            $manager = $this->main->getEnchantManager();
            $enchant = $manager->getByKey($args['enchant']);
            if($enchant === null){
                $this->main->getMessages()->send($sender, 'invalid_enchantment');
                return;
            }

            $item = $sender->getInventory()->getItemInHand();

            if($item->isNull()){
                $this->main->getMessages()->send($sender, 'item_null');
                return;
            }

            $item->removeEnchantment($enchant);
            $item = $manager->rebuildLore($item);
            $sender->getInventory()->setItemInHand($item);
            $this->main->getMessages()->send($sender, 'enchant_removed', ['key' => $args['enchant']]);
        }
    }
}