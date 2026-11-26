<?php

declare(strict_types=1);

namespace pup\customenchants\addons;

use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pup\customenchants\Main;
use pup\customenchants\MessageService;
use Throwable;

final class AddonManager
{
	private const string MANIFEST = 'addons.json';

	private readonly string $directory;

	/** @var array<string, array{repository: string, tag: string, sha256: string, verified: bool, plugin: ?string}> */
	private array $manifest = [];

	/** @var array<string, true> */
	private array $pending = [];

	/** @var array<string, array{target: string, needs: list<string>, requestedBy: string}> */
	private array $waiting = [];

	/** @var array<string, string> plugin name => addon file name of a dependency being installed */
	private array $dependencyFiles = [];

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
			MessageService::send($sender, 'addon_unknown', ['name' => $input]);
			return;
		}
		[$name, $repository] = $resolved;

		if (isset($this->pending[$name])) {
			MessageService::send($sender, 'addon_already_downloading', ['name' => $name]);
			return;
		}
		$this->download($sender, $name, $repository);
	}

	private function download(CommandSender $sender, string $name, string $repository): void
	{
		$this->pending[$name] = true;
		MessageService::send($sender, 'addon_downloading', ['name' => $name, 'repository' => $repository]);
		$this->plugin->getServer()->getAsyncPool()->submitTask(new AddonDownloadTask(
			$name,
			$repository,
			$this->directory . $name . '.phar',
			$sender instanceof Player ? $sender->getName() : ''
		));
	}

	/** @return array{0: string, 1: string}|null */
	public function resolveDependency(string $plugin, string $parentRepository): ?array
	{
		$resolved = $this->resolve($plugin);
		if ($resolved !== null) {
			return $resolved;
		}

		$config = $this->plugin->getConfig()->getNested('addons', []);
		$config = is_array($config) ? $config : [];
		$repositories = is_array($config['repositories'] ?? null) ? $config['repositories'] : [];
		foreach ($repositories as $name => $repository) {
			if (is_string($repository) && self::isRepository($repository) && strcasecmp(explode('/', $repository)[1], $plugin) === 0) {
				return [self::fileName((string) $name), $repository];
			}
		}

		$candidate = explode('/', $parentRepository)[0] . '/' . $plugin;
		if (($config['allow_any_repository'] ?? false) === true && self::isRepository($candidate)) {
			return [self::fileName($plugin), $candidate];
		}
		return null;
	}

	/** @param array<string, mixed> $result */
	public function onDownloaded(string $name, string $repository, string $target, string $requestedBy, array $result): void
	{
		unset($this->pending[$name]);
		$sender = $this->findSender($requestedBy);

		if (isset($result['error'])) {
			MessageService::send($sender, 'addon_download_failed', ['name' => $name, 'error' => (string) $result['error']]);
			$this->settleWaiting();
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

		MessageService::send($sender, $verified ? 'addon_downloaded_verified' : 'addon_downloaded_unverified', ['name' => $name, 'tag' => $tag, 'sha256' => substr($sha, 0, 12)]);

		if ($alreadyLoaded !== null) {
			MessageService::send($sender, 'addon_restart_to_update', ['name' => $name]);
			$this->settleWaiting();
			return;
		}

		if (!@rename((string) $result['file'], $target)) {
			MessageService::send($sender, 'addon_saved_restart', ['name' => $name]);
			$this->settleWaiting();
			return;
		}

		$missing = $this->missingDependencies($target);
		if ($missing === []) {
			$this->load($name, $target, $requestedBy);
			$this->settleWaiting();
			return;
		}

		$this->waiting[$name] = ['target' => $target, 'needs' => $missing, 'requestedBy' => $requestedBy];
		$installable = array_values(array_filter(
			$missing,
			fn(string $dependency): bool => $this->resolveDependency($dependency, $repository) !== null
		));
		if ($installable !== []) {
			MessageService::send($sender, 'addon_installing_dependencies', ['name' => $name, 'dependencies' => implode(', ', $installable)]);
		}
		foreach ($installable as $dependency) {
			$this->installDependency($sender, $dependency, $repository);
		}
		$this->settleWaiting();
	}

	/**
	 * @return list<string> hard dependencies of the phar that are not loaded yet
	 */
	private function missingDependencies(string $file): array
	{
		$server = $this->plugin->getServer();

		try {
			$description = new PharPluginLoader($server->getLoader())->getPluginDescription($file);
		} catch (Throwable) {
			return [];
		}

		if ($description === null) {
			return [];
		}

		$manager = $server->getPluginManager();

		return array_values(array_filter(
			$description->getDepend(),
			static fn(string $dependency): bool =>
				!self::isIgnoredDependency($dependency) &&
				$manager->getPlugin($dependency) === null
		));
	}

	private function installDependency(CommandSender $sender, string $plugin, string $parentRepository): void
	{
		$file = $this->dependencyFiles[$plugin] ?? null;
		if ($file !== null && (isset($this->pending[$file]) || isset($this->waiting[$file]))) {
			return;
		}
		$resolved = $this->resolveDependency($plugin, $parentRepository);
		if ($resolved === null) {
			return;
		}
		[$name, $repository] = $resolved;
		$this->dependencyFiles[$plugin] = $name;
		if (!isset($this->pending[$name])) {
			$this->download($sender, $name, $repository);
		}
	}

	private static function isIgnoredDependency(string $dependency): bool
	{
		//Should be only CustomEnchants but im lazy :D!
		return in_array(
			strtolower($dependency),
			['customenchant', 'customenchants'],
			true
		);
	}

	private function settleWaiting(): void
	{
		$manager = $this->plugin->getServer()->getPluginManager();
		do {
			$changed = false;
			foreach ($this->waiting as $name => $entry) {
				$blocker = null;
				$ready = true;
				foreach ($entry['needs'] as $dependency) {
					$loaded = $manager->getPlugin($dependency);
					if ($loaded !== null && $loaded->isEnabled()) {
						continue;
					}
					$ready = false;
					$file = $this->dependencyFiles[$dependency] ?? null;
					$downloading = $file !== null && isset($this->pending[$file]);
					$queued = $file !== null && isset($this->waiting[$file]);
					if ($loaded !== null || (!$downloading && !$queued)) {
						$blocker = $dependency;
						break;
					}
				}

				if ($blocker !== null) {
					unset($this->waiting[$name]);
					$changed = true;
					$key = $manager->getPlugin($blocker) !== null
						? 'addon_dependency_disabled'
						: (isset($this->dependencyFiles[$blocker]) ? 'addon_dependency_failed' : 'addon_dependency_unlisted');
					MessageService::send($this->findSender($entry['requestedBy']), $key, ['name' => $name, 'dependency' => $blocker]);
				} elseif ($ready) {
					unset($this->waiting[$name]);
					$changed = true;
					$this->load($name, $entry['target'], $entry['requestedBy']);
				}
			}
		} while ($changed);
	}

	private function load(string $name, string $target, string $requestedBy): void
	{
		$sender = $this->findSender($requestedBy);
		try {
			$errors = 0;
			$loaded = $this->plugin->getServer()->getPluginManager()->loadPlugins($target, $errors);
		} catch (Throwable $e) {
			MessageService::send($sender, 'addon_load_error', ['name' => $name, 'error' => $e->getMessage()]);
			return;
		}

		if ($loaded === []) {
			MessageService::send($sender, 'addon_load_refused', ['name' => $name]);
			return;
		}

		foreach ($loaded as $addon) {
			$this->manifest[$name]['plugin'] = $addon->getName();
			$enabled = $this->enable($addon);
			MessageService::send($sender, $enabled ? 'addon_enabled' : 'addon_enable_failed', ['name' => $name, 'plugin' => $addon->getDescription()->getFullName()]);
		}
		$this->saveManifest();
	}

	public function remove(CommandSender $sender, string $name): void
	{
		$name = self::fileName($name);
		$file = $this->directory . $name . '.phar';
		if (!isset($this->manifest[$name]) && !is_file($file)) {
			MessageService::send($sender, 'addon_not_installed', ['name' => $name]);
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

		MessageService::send(
			$sender,
			$deleted ? ($loaded !== null ? 'addon_removed_restart' : 'addon_removed') : 'addon_remove_locked',
			['name' => $name, 'file' => basename($file)]
		);
	}

	public function list(CommandSender $sender): void
	{
		$files = array_map(static fn(string $f): string => basename($f, '.phar'), glob($this->directory . '*.phar') ?: []);
		$names = array_unique([...array_keys($this->manifest), ...$files]);
		sort($names);

		if ($names === []) {
			MessageService::send($sender, 'addon_list_empty');
		}
		foreach ($names as $name) {
			$info = $this->manifest[$name] ?? null;
			$loaded = $this->findLoaded($name);
			$state = MessageService::getInstance()->get($loaded !== null && $loaded->isEnabled() ? 'addon_state_enabled' : 'addon_state_not_loaded');
			MessageService::send($sender, $info !== null ? 'addon_list_entry' : 'addon_list_entry_manual', [
				'name' => $name,
				'repository' => (string) ($info['repository'] ?? ''),
				'tag' => (string) ($info['tag'] ?? ''),
				'state' => $state,
			]);
		}

		$config = $this->plugin->getConfig()->getNested('addons.repositories', []);
		if (is_array($config) && $config !== []) {
			MessageService::send($sender, 'addon_list_available', ['names' => implode(', ', array_keys($config))]);
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
