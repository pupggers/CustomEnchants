<?php

namespace pup\customenchants;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pup\placeholderapi\PlaceholderApi;

final class MessageService
{
    private const array DEFAULTS = [
        'invalid_enchantment' => '{red}That enchantment is unavailable.', 'item_null' => '{red}Hold an item first.',
        'level_exceeds_max' => '{red}That level exceeds the maximum of {max_level}.', 'cannot_apply' => '{red}That enchantment cannot be applied to this item.',
        'enchant_applied' => '{green}Applied {enchant_name} {level} to your item.', 'enchant_removed' => '{green}Removed {enchant_name} from your item.',
        'tool_repaired' => '{green}Your tool has been repaired!', 'book_max_level' => '{red}This enchantment is already at its maximum level.',
        'book_combine_mismatch' => '{red}You can only combine books with the same enchantment and level.', 'durable_items_only' => '{red}You can only enchant durable items.',
        'item_has_equal_or_higher' => '{red}This item already has that enchantment at an equal or higher level.', 'item_enchant_max_level' => '{red}This enchantment is already at its maximum level on this item.',
        'no_enchants_for_rarity' => '{red}No enabled enchantments are available for this rarity.', 'books_created' => '{green}Created {amount} enchantment books!',
        'inventory_full_books' => '{red}Your inventory is full; {amount} book(s) were dropped.', 'black_scroll_durable_only' => '{red}Only durable items can be modified with a Black Scroll.',
        'black_scroll_no_enchants' => '{red}That item has no enchantments to remove.', 'black_scroll_success' => '{green}Successfully removed an enchantment!',
        'black_scroll_failure' => '{red}Failed to remove an enchantment.', 'white_scroll_durable_only' => '{red}That item must be durable.',
        'white_scroll_protected' => '{red}That item is already protected.', 'item_processing_error' => '{red}An error occurred while processing that item.',
    ];
    private static self $instance;

    /** @var array<string, mixed> */
    private array $messages;

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

    /** @param array<string, scalar> $replacements */
    public static function send(Player $player, string $key, array $replacements = []): void
    {
        $message = self::$instance->messages[$key] ?? self::DEFAULTS[$key] ?? '';
        if (!is_string($message) || $message === '') {
            return;
        }

        $player->sendMessage(self::$instance->format($message, $player, $replacements));
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