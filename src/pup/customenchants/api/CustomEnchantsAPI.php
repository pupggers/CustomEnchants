<?php

declare(strict_types=1);

namespace pup\customenchants\api;

use JsonException;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pup\customenchants\CustomEnchantInterface;
use pup\customenchants\EnchantManager;
use pup\customenchants\Main;

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
				$entry = $definition->defaults + ['enabled' => true];
				$config[$definition->key] = $entry;
				$changed = true;
			}

			$enchant = $manager->register($definition, $addon, $entry);
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
}
