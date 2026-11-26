<?php

namespace pup\customenchants;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pup\customenchants\addons\AddonManager;
use pup\customenchants\commands\EnchanterComand;
use pup\customenchants\items\ItemListener;
use pup\customenchants\utils\Rarity;
use pup\placeholderapi\PlaceholderApi;

class Main extends PluginBase
{
    private static self $instance;
    private EnchantManager $enchantManager;
    private MessageService $messages;
    private AddonManager $addonManager;

    public static function getInstance()
    : Main
    {
        return self::$instance;
    }

    public function onLoad()
    : void
    {
        self::$instance = $this;
    }

    /** @throws HookAlreadyRegistered */
    public function onEnable()
    : void
    {
        if(!PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }

        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }

        $this->saveResource("enchantments.json");
        $this->saveResource("items.json");
        $this->saveDefaultConfig();
        PlaceholderApi::initialize();
        $messages = $this->getConfig()->get('messages', []);
        $this->messages = new MessageService(is_array($messages) ? $messages : []);
        $this->enchantManager = new EnchantManager($this);

        $this->getServer()->getPluginManager()->registerEvents(new EnchantListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ItemListener(), $this);
        $this->getServer()->getCommandMap()->register("CustomEnchants", new EnchanterComand($this));

        $this->addonManager = new AddonManager($this);
        $this->addonManager->loadInstalled();

    }

    public function getEnchantManager(): EnchantManager
    {
        return $this->enchantManager;
    }

    public function getAddonManager(): AddonManager
    {
        return $this->addonManager;
    }

    public function getMessages(): MessageService
    {
        return $this->messages;
    }
}
