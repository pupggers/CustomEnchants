<?php

declare(strict_types=1);

namespace pup\customenchants\addons;

use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use pup\customenchants\Main;
use Throwable;

final class AddonManager
{
	private const string MANIFEST = 'addons.json';

	private readonly string $directory;

	/** @var array<string, array{repository: string, tag: string, sha256: string, verified: bool, plugin: ?string}> */
	private array $manifest = [];

	/** @var array<string, true> */
	private array $pending = [];

	public function __construct(private readonly Main $plugin)
	{
		$this->directory = $plugin->getDataFolder() . 'addons' . DIRECTORY_SEPARATOR;
		if (!is_dir($this->directory)) {
			@mkdir($this->directory, 0777, true);
		}
		$this->loadManifest();
	}

	public function getDirectory(): string
	{
		return $this->directory;
	}

	public function loadInstalled(): void
	{
		foreach (glob($this->directory . '*.phar.new') ?: [] as $update) {
			$target = substr($update, 0, -4);
			if (!@rename($update, $target)) {
				$this->plugin->getLogger()->warning('Could not apply addon update ' . basename($update));
			}
		}

		if ((glob($this->directory . '*.phar') ?: []) === []) {
			return;
		}

		$errors = 0;
		$loaded = $this->plugin->getServer()->getPluginManager()->loadPlugins($this->directory, $errors);
		foreach ($loaded as $addon) {
			$this->enable($addon);
		}
		if ($errors > 0) {
			$this->plugin->getLogger()->warning("$errors addon(s) failed to load; see the errors above.");
		}
	}

	private function enable(Plugin $addon): bool
	{
		$manager = $this->plugin->getServer()->getPluginManager();
		if (!$manager->isPluginEnabled($addon)) {
			$manager->enablePlugin($addon);
		}
		$ok = $manager->isPluginEnabled($addon);
		if ($ok) {
			$this->plugin->getLogger()->info('Loaded addon ' . $addon->getDescription()->getFullName());
		}
		return $ok;
	}

	/** @return array{0: string, 1: string}|null */
	public function resolve(string $input): ?array
	{
		$config = $this->plugin->getConfig()->getNested('addons', []);
		$config = is_array($config) ? $config : [];
		$repositories = is_array($config['repositories'] ?? null) ? $config['repositories'] : [];

		$alias = strtolower($input);
		foreach ($repositories as $name => $repository) {
			if (is_string($name) && is_string($repository) && strtolower($name) === $alias && self::isRepository($repository)) {
				return [self::fileName($name), $repository];
			}
		}

		if (self::isRepository($input)) {
			foreach ($repositories as $name => $repository) {
				if (is_string($repository) && strcasecmp($repository, $input) === 0) {
					return [self::fileName((string) $name), $repository];
				}
			}
			if (($config['allow_any_repository'] ?? false) === true) {
				return [self::fileName(explode('/', $input)[1]), $input];
			}
		}

		return null;
	}

	public function install(CommandSender $sender, string $input): void
	{
		$resolved = $this->resolve($input);
		if ($resolved === null) {
			$sender->sendMessage(TF::RED . "Unknown addon '$input'. Add it to addons.repositories in config.yml"
				. " (or set addons.allow_any_repository and use owner/repo).");
			return;
		}
		[$name, $repository] = $resolved;

		if (isset($this->pending[$name])) {
			$sender->sendMessage(TF::YELLOW . "$name is already downloading.");
			return;
		}
		$this->pending[$name] = true;

		$sender->sendMessage(TF::GRAY . "Downloading $name from github.com/$repository ...");
		$this->plugin->getServer()->getAsyncPool()->submitTask(new AddonDownloadTask(
			$name,
			$repository,
			$this->directory . $name . '.phar',
			$sender instanceof Player ? $sender->getName() : ''
		));
	}

	/** @param array<string, mixed> $result */
	public function onDownloaded(string $name, string $repository, string $target, string $requestedBy, array $result): void
	{
		unset($this->pending[$name]);
		$sender = $this->findSender($requestedBy);

		if (isset($result['error'])) {
			$sender->sendMessage(TF::RED . "Could not install $name: {$result['error']}");
			return;
		}

		$tag = (string) $result['tag'];
		$sha = (string) $result['sha256'];
		$verified = (bool) $result['verified'];
		$alreadyLoaded = $this->findLoaded($name);

		$this->manifest[$name] = [
			'repository' => $repository,
			'tag' => $tag,
			'sha256' => $sha,
			'verified' => $verified,
			'plugin' => $alreadyLoaded?->getName() ?? ($this->manifest[$name]['plugin'] ?? null),
		];
		$this->saveManifest();

		$check = $verified ? TF::GREEN . 'checksum verified' : TF::YELLOW . 'no checksum published';
		$sender->sendMessage(TF::GREEN . "Downloaded $name $tag ($check" . TF::GREEN . ', sha256 ' . substr($sha, 0, 12) . '...)');

		if ($alreadyLoaded !== null) {
			$sender->sendMessage(TF::YELLOW . 'Restart the server to switch to the new version.');
			return;
		}

		if (!@rename((string) $result['file'], $target)) {
			$sender->sendMessage(TF::YELLOW . 'Saved; restart the server to load it.');
			return;
		}

		try {
			$errors = 0;
			$loaded = $this->plugin->getServer()->getPluginManager()->loadPlugins($target, $errors);
		} catch (Throwable $e) {
			$sender->sendMessage(TF::RED . "Installed, but loading failed: {$e->getMessage()}. Restart to retry.");
			return;
		}

		if ($loaded === []) {
			$sender->sendMessage(TF::RED . 'Installed, but PocketMine refused to load it (check the console). Restart to retry.');
			return;
		}

		foreach ($loaded as $addon) {
			$this->manifest[$name]['plugin'] = $addon->getName();
			$this->enable($addon)
				? $sender->sendMessage(TF::GREEN . 'Enabled ' . $addon->getDescription()->getFullName() . '.')
				: $sender->sendMessage(TF::RED . $addon->getName() . ' failed to enable (check the console).');
		}
		$this->saveManifest();
	}

	public function remove(CommandSender $sender, string $name): void
	{
		$name = self::fileName($name);
		$file = $this->directory . $name . '.phar';
		if (!isset($this->manifest[$name]) && !is_file($file)) {
			$sender->sendMessage(TF::RED . "Addon '$name' is not installed.");
			return;
		}

		$loaded = $this->findLoaded($name);
		if ($loaded !== null && $loaded->isEnabled()) {
			$this->plugin->getServer()->getPluginManager()->disablePlugin($loaded);
		}

		@unlink($file . '.new');
		$deleted = !is_file($file) || @unlink($file);
		unset($this->manifest[$name]);
		$this->saveManifest();

		$sender->sendMessage($deleted
			? TF::GREEN . "Removed $name." . ($loaded !== null ? ' Restart to unload its enchants completely.' : '')
			: TF::YELLOW . "Disabled $name, but the file is in use; it will be gone after you delete " . basename($file) . ' while the server is off.');
	}

	public function list(CommandSender $sender): void
	{
		$files = array_map(static fn(string $f): string => basename($f, '.phar'), glob($this->directory . '*.phar') ?: []);
		$names = array_unique([...array_keys($this->manifest), ...$files]);
		sort($names);

		if ($names === []) {
			$sender->sendMessage(TF::GRAY . 'No addons installed. Use /ce addon install <name>.');
		}
		foreach ($names as $name) {
			$info = $this->manifest[$name] ?? null;
			$loaded = $this->findLoaded($name);
			$state = $loaded !== null && $loaded->isEnabled() ? TF::GREEN . 'enabled' : TF::RED . 'not loaded';
			$source = $info !== null ? " {$info['repository']} {$info['tag']}" : ' (installed by hand)';
			$sender->sendMessage(TF::WHITE . $name . TF::GRAY . $source . ' - ' . $state);
		}

		$config = $this->plugin->getConfig()->getNested('addons.repositories', []);
		if (is_array($config) && $config !== []) {
			$sender->sendMessage(TF::GRAY . 'Available: ' . implode(', ', array_keys($config)));
		}
	}

	private function findLoaded(string $name): ?Plugin
	{
		$pluginName = $this->manifest[$name]['plugin'] ?? null;
		$manager = $this->plugin->getServer()->getPluginManager();
		return ($pluginName !== null ? $manager->getPlugin($pluginName) : null) ?? $manager->getPlugin($name);
	}

	private function findSender(string $name): CommandSender
	{
		$server = $this->plugin->getServer();
		$player = $name !== '' ? $server->getPlayerExact($name) : null;
		return $player ?? new ConsoleCommandSender($server, $server->getLanguage());
	}

	private static function isRepository(string $value): bool
	{
		return preg_match('~^[A-Za-z0-9-]+/[A-Za-z0-9._-]+$~', $value) === 1;
	}

	private static function fileName(string $name): string
	{
		return strtolower((string) preg_replace('~[^A-Za-z0-9_-]~', '', $name));
	}

	private function loadManifest(): void
	{
		$path = $this->directory . self::MANIFEST;
		if (!is_file($path)) {
			return;
		}
		try {
			$data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
			$this->manifest = is_array($data) ? $data : [];
		} catch (JsonException) {
			$this->plugin->getLogger()->warning('addons/addons.json is unreadable; addon versions will show as unknown.');
		}
	}

	private function saveManifest(): void
	{
		file_put_contents(
			$this->directory . self::MANIFEST,
			json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}
}
