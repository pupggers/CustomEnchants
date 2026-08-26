<?php

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;
use pup\customenchants\Main;

final class AddEnchantSub extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "add", "Add a new enchantment");
        $this->setPermission("enchant.command.add");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare()
    : void
    {
        $this->registerArgument(0, new RawStringArgument('enchant'));
        $this->registerArgument(1, new IntegerArgument('level', true));
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
            $level = $args['level'] ?? 1;

            $level = (int)abs($level);
            $config = Main::getInstance()->getConfig();

            if ($config->get("max_level", true) && $level > $enchant->getMaxLevel()) {
                $this->main->getMessages()->send($sender, 'level_exceeds_max', ['max_level' => $enchant->getMaxLevel()]);
                return;
            }

            if (!$manager->canApply($args['enchant'], $item)) {
                $this->main->getMessages()->send($sender, 'cannot_apply');
                return;
            }

            $item->addEnchantment(new EnchantmentInstance($enchant, $level));
            $item = $manager->rebuildLore($item);
            $sender->getInventory()->setItemInHand($item);
            $this->main->getMessages()->send($sender, 'enchant_applied', ['key' => $args['enchant'], 'level' => $level]);
        }
    }
}