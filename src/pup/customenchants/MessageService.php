<?php

namespace pup\customenchants;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pup\placeholderapi\PlaceholderApi;

final class MessageService
{
    private const array DEFAULTS = [
        'invalid_enchantment' => '{red}That enchantment is unavailable.', 'item_null' => '{red}Hold an item first.',
        'level_exceeds_max' => '{red}That level exceeds the maximum of {max_level}.', 'cannot_apply' => '{red}That enchantment cannot be applied to this item.',
        'enchant_applied' => '{green}Applied {enchant_name} {level} to your item.', 'enchant_removed' => '{green}Removed {enchant_name} from your item.',
        'enchant_not_on_item' => '{red}That item does not have {enchant_name}.',
        'shop_bought' => '{green}Bought {item}{green} for {price}.',
        'shop_cannot_afford' => '{red}You need {price} to buy that.',
        'shop_inventory_full' => '{red}Make room in your inventory first.',
        'shop_price_lore' => '{gray}Price: {yellow}{price}',
        'shop_buy_lore' => '{green}Click to buy',
        'tinkerer_traded' => '{green}Traded {books} book(s) for {dust} Magic Dust.',
        'tinkerer_nothing' => '{red}Put enchantment books in first.',
        'tinkerer_disabled' => '{red}Magic Dust is disabled on this server.',
        'tinkerer_button_name' => '{gold}Tinkerer',
        'tinkerer_button_lore' => '{gray}Trade enchantment books for Magic Dust',
        'tinkerer_back_name' => '{yellow}Back to the Enchanter',
        'tinkerer_confirm_name' => '{green}Trade for Magic Dust',
        'tinkerer_confirm_lore' => '{gray}{books} book(s) -> {light_purple}{dust} Magic Dust',
        'enchant_conflict' => "{red}{enchant_name} can't be combined with {other_name}.",
        'enchant_requires' => '{red}{enchant_name} requires {other_name} {required_level} on the item first.',
        'enchant_forced' => '{yellow}Forced {enchant_name} past a conflict/requirement check.',
        'tool_repaired' => '{green}Your tool has been repaired!', 'book_max_level' => '{red}This enchantment is already at its maximum level.',
        'book_combine_mismatch' => '{red}You can only combine books with the same enchantment and level.', 'durable_items_only' => '{red}You can only enchant durable items.',
        'item_has_equal_or_higher' => '{red}This item already has that enchantment at an equal or higher level.', 'item_enchant_max_level' => '{red}This enchantment is already at its maximum level on this item.',
        'no_enchants_for_rarity' => '{red}No enabled enchantments are available for this rarity.', 'books_created' => '{green}Created {amount} enchantment books!',
        'inventory_full_books' => '{red}Your inventory is full; {amount} book(s) were dropped.', 'black_scroll_durable_only' => '{red}Only durable items can be modified with a Black Scroll.',
        'black_scroll_no_enchants' => '{red}That item has no enchantments to remove.', 'black_scroll_success' => '{green}Successfully removed an enchantment!',
        'black_scroll_failure' => '{red}Failed to remove an enchantment.', 'white_scroll_durable_only' => '{red}That item must be durable.',
        'white_scroll_protected' => '{red}That item is already protected.', 'item_processing_error' => '{red}An error occurred while processing that item.',
        'black_scroll_disabled' => "{red}Black Scrolls can't remove enchantments right now.",
        'book_failed_kept' => '{yellow}The enchantment failed, but your item survived.',
        'book_failed_protected' => '{gold}The enchantment failed, but your protection scroll saved the item!',
        'book_failed_holy_protected' => '{gold}The enchantment failed, but your Holy protection saved the item!',
        'book_failed_destroyed' => '{red}The enchantment failed and your item was destroyed!',
        'dust_opened' => '{green}The Magic Dust revealed: {dust_name}{green}!',
        'dusts_opened' => '{green}Revealed {amount} dusts!',
        'inventory_full_dust' => '{red}Your inventory is full; {amount} dust(s) were dropped.',
        'dust_books_only' => '{red}Dust can only be combined with an enchantment book.',
        'dust_maxed' => "{red}This book can't be improved any further with that dust.",
        'dust_success_boosted' => "{green}The book's success rate is now {success}%.",
        'dust_destroy_reduced' => "{green}The book's destroy rate is now {destroy}%.",
        'dust_nothing' => '{gray}The Bio Product fizzled out. Nothing happened.',
        'addon_usage' => '{red}Usage: /ce addon <list | install <name> | remove <name>>',
        'addon_unknown' => "{red}Unknown addon '{name}'. Add it to addons.repositories in config.yml (or set addons.allow_any_repository and use owner/repo).",
        'addon_already_downloading' => '{yellow}{name} is already downloading.',
        'addon_downloading' => '{gray}Downloading {name} from github.com/{repository} ...',
        'addon_download_failed' => '{red}Could not install {name}: {error}',
        'addon_downloaded_verified' => '{green}Downloaded {name} {tag} (checksum verified, sha256 {sha256}...)',
        'addon_downloaded_unverified' => '{green}Downloaded {name} {tag} ({yellow}no checksum published{green}, sha256 {sha256}...)',
        'addon_restart_to_update' => '{yellow}Restart the server to switch to the new version.',
        'addon_saved_restart' => '{yellow}Saved; restart the server to load it.',
        'addon_installing_dependencies' => '{gray}{name} needs {dependencies}; installing first.',
        'addon_dependency_disabled' => '{red}{name} was not loaded: {dependency} is installed but not enabled. It will load on the next restart once {dependency} is running.',
        'addon_dependency_failed' => '{red}{name} was not loaded: {dependency} could not be installed. It will load on the next restart once {dependency} is there.',
        'addon_dependency_unlisted' => '{red}{name} was not loaded: {dependency} is not in addons.repositories; install it yourself. It will load on the next restart once {dependency} is there.',
        'addon_load_error' => '{red}Installed {name}, but loading failed: {error}. Restart to retry.',
        'addon_load_refused' => '{red}Installed {name}, but PocketMine refused to load it (check the console). Restart to retry.',
        'addon_enabled' => '{green}Enabled {plugin}.',
        'addon_enable_failed' => '{red}{plugin} failed to enable (check the console).',
        'addon_not_installed' => "{red}Addon '{name}' is not installed.",
        'addon_removed' => '{green}Removed {name}.',
        'addon_removed_restart' => '{green}Removed {name}. Restart to unload its enchants completely.',
        'addon_remove_locked' => '{yellow}Disabled {name}, but the file is in use; it will be gone after you delete {file} while the server is off.',
        'addon_list_empty' => '{gray}No addons installed. Use /ce addon install <name>.',
        'addon_list_entry' => '{white}{name} {gray}{repository} {tag} - {state}',
        'addon_list_entry_manual' => '{white}{name} {gray}(installed by hand) - {state}',
        'addon_state_enabled' => '{green}enabled',
        'addon_state_not_loaded' => '{red}not loaded',
        'addon_list_available' => '{gray}Available: {names}'
    ];
    private static self $instance;

    /** @var array<string, mixed> */
    private array $messages;

    /** @var array<string, string> */
    private array $registered = [];

    /** @param array<string, mixed> $messages */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * Adds message keys owned by an addon. Precedence: core config.yml messages, then $overrides, then $defaults.
     *
     * @param array<string, string> $defaults
     * @param array<string, mixed> $overrides
     */
    public function register(array $defaults, array $overrides = []): void
    {
        foreach ($defaults as $key => $default) {
            $override = $overrides[$key] ?? null;
            $this->registered[$key] = is_string($override) ? $override : $default;
        }
    }

    /** The unformatted template for $key; an empty string disables the message. */
    public function get(string $key, string $default = ''): string
    {
        $message = $this->messages[$key] ?? $this->registered[$key] ?? self::DEFAULTS[$key] ?? $default;
        return is_string($message) ? $message : $default;
    }

    /** @param array<string, scalar> $replacements */
    public function render(string $key, ?Player $player = null, array $replacements = []): string
    {
        $message = $this->get($key);
        return $message === '' ? '' : $this->format($message, $player, $replacements);
    }

    /** @param array<string, scalar> $replacements */
    public static function send(CommandSender $to, string $key, array $replacements = []): void
    {
        $message = self::$instance->render($key, $to instanceof Player ? $to : null, $replacements);
        if ($message !== '') {
            $to->sendMessage($message);
        }
    }

    /** @param array<string, scalar> $replacements */
    public static function popup(Player $player, string $key, array $replacements = []): void
    {
        $message = self::$instance->render($key, $player, $replacements);
        if ($message !== '') {
            $player->sendPopup($message);
        }
    }

    /** @param array<string, scalar> $replacements */
    public function format(string $message, ?Player $player = null, array $replacements = []): string
    {
        $tokens = [];
        foreach ($replacements as $key => $value) {
            $tokens['{' . $key . '}'] = (string) $value;
        }

        return PlaceholderApi::parse(strtr($message, $tokens), $player);
    }
}
