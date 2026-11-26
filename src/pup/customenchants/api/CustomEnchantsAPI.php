<?php

declare(strict_types=1);

namespace pup\customenchants\api;

use Closure;
use InvalidArgumentException;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\entity\Human;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pup\customenchants\CustomEnchantInterface;
use pup\customenchants\EnchantManager;
use pup\customenchants\Main;
use pup\customenchants\MessageService;
use pup\customenchants\utils\Rarity;
use pup\placeholderapi\placeholder\ClosurePlaceholder;
use pup\placeholderapi\PlaceholderApi;

final class CustomEnchantsAPI
{
	public static function getManager(): EnchantManager
	{
		return Main::getInstance()->getEnchantManager();
	}

	/**
	 * @param list<EnchantDefinition> $definitions
	 * @return array<string, Enchantment&CustomEnchantInterface>
	 */
	public static function registerAll(PluginBase $addon, array $definitions, string $configFile = 'enchantments.json'): array
	{
		$manager = self::getManager();
		$path = $addon->getDataFolder() . $configFile;

		if (!is_file($path)) {
			$addon->saveResource($configFile);
		}

		$config = [];
		if (is_file($path)) {
			try {
				$decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
				$config = is_array($decoded) ? $decoded : [];
			} catch (JsonException $e) {
				$addon->getLogger()->error("$configFile is not valid JSON ({$e->getMessage()}); using defaults.");
			}
		}

		$changed = false;
		$registered = [];
		foreach ($definitions as $definition) {
			$entry = $config[$definition->key] ?? null;
			if (!is_array($entry)) {
				$entry = array_replace(['enabled' => true], $definition->defaults);
				unset($entry['has_chance']);
				$config[$definition->key] = $entry;
				$changed = true;
			}

			try {
				$enchant = $manager->register($definition, $addon, $entry);
			} catch (InvalidArgumentException $e) {
				$addon->getLogger()->warning("Skipped enchant '$definition->key': {$e->getMessage()}");
				continue;
			}
			if ($enchant !== null) {
				$registered[$definition->key] = $enchant;
			}
		}

		if ($changed) {
			@mkdir(dirname($path), 0777, true);
			file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		}

		$addon->getLogger()->info('Registered ' . count($registered) . '/' . count($definitions) . ' custom enchants.');

		return $registered;
	}

	public static function check(Item $item, string $key, int $level): ApplyResult
	{
		return self::getManager()->check($item, $key, $level);
	}

	public static function apply(Item $item, string $key, int $level, bool $force = false): ApplyResult
	{
		return self::getManager()->apply($item, $key, $level, $force);
	}

	/** @return array<string, int> */
	public static function getEnchants(Item $item): array
	{
		return self::getManager()->getKeysOnItem($item);
	}

	/**
	 * Register a new rarity tier before registering enchants that use it.
	 * The value is saved on items, so keep it fixed once released.
	 */
	public static function registerRarity(PluginBase $addon, string $name, int $value, string $displayName, string $color, int $weight = 0): Rarity
	{
		$existing = Rarity::get($name);
		if ($existing !== null && $existing->value === $value) {
			return $existing;
		}
		return Rarity::register($name, $value, $displayName, $color, $weight, $addon);
	}

	/** @return array<int, int> */
	public static function getWornLevels(Human $holder, string $key): array
	{
		$key = EnchantManager::normalizeKey($key);
		$levels = [];
		foreach ($holder->getArmorInventory()->getContents() as $slot => $armor) {
			$level = self::getManager()->getKeysOnItem($armor)[$key] ?? 0;
			if ($level > 0) {
				$levels[$slot] = $level;
			}
		}
		return $levels;
	}

	public static function getTotalWornLevel(Human $holder, string $key): int
	{
		return array_sum(self::getWornLevels($holder, $key));
	}

	/**
	 * Registers an addon's message keys. Server owners can override them under "messages:" in the addon's
	 * config.yml (saved from its resources if missing) or in CustomEnchant's config.yml.
	 *
	 * @param array<string, string> $defaults
	 */
	public static function registerMessages(PluginBase $addon, array $defaults): void
	{
		$addon->saveDefaultConfig();
		$overrides = $addon->getConfig()->get('messages', []);
		Main::getInstance()->getMessages()->register($defaults, is_array($overrides) ? $overrides : []);
	}

	/** @param array<string, scalar> $replacements */
	public static function sendMessage(CommandSender $to, string $key, array $replacements = []): void
	{
		MessageService::send($to, $key, $replacements);
	}

	/** @param array<string, scalar> $replacements */
	public static function sendPopup(Player $player, string $key, array $replacements = []): void
	{
		MessageService::popup($player, $key, $replacements);
	}

	/**
	 * Formats a message key, or raw text when $isKey is false, through PlaceholderAPI.
	 *
	 * @param array<string, scalar> $replacements
	 */
	public static function formatMessage(string $message, ?Player $player = null, array $replacements = [], bool $isKey = true): string
	{
		$messages = Main::getInstance()->getMessages();
		return $isKey ? $messages->render($message, $player, $replacements) : $messages->format($message, $player, $replacements);
	}

	/**
	 * Adds a {identifier} or {identifier:params} placeholder to CustomEnchant's PlaceholderAPI.
	 *
	 * @param Closure(?Player, ?string): string $handler
	 */
	public static function registerPlaceholder(string $identifier, Closure $handler, bool $requiresPlayer = false, bool $supportsParameters = false): bool
	{
		return PlaceholderApi::registerCustomPlaceholder(new ClosurePlaceholder($identifier, $handler, $requiresPlayer, $supportsParameters));
	}
}