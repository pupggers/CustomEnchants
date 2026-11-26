<?php

declare(strict_types=1);

namespace pup\customenchants;

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Throwable;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Item;
use pocketmine\item\enchantment\{
	Enchantment,
	EnchantmentInstance,
	ItemEnchantmentTagRegistry,
};
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pup\customenchants\api\{
	ApplyResult,
	ApplyStatus,
	EnchantDefinition,
};
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\utils\{
	EnchantPlaceholders,
	EnchantTasks,
	Rarity,
	Settings,
};

final class EnchantManager{
	public const int MIN_SAFE_LOW_BYTE = 64;
	public const int MAX_ID = 32767;

	/** @var array<string, array<string, mixed>> */
	private array $data;

	/** @var array<string, EnchantDefinition> */
	private array $definitions = [];

	/** @var array<string, array<string, mixed>> */
	private array $configs = [];

	/** @var array<string, Plugin> */
	private array $owners = [];

	/** @var array<string, Enchantment&CustomEnchantInterface> */
	private array $registered = [];

	/** @var array<int, string> */
	private array $keysByObject = [];

	/** @var array<string, array<string, string>>|null */
	private ?array $conflictGraph = null;

	private int $nextId = 1021;

	/** @var array<string, int> */
	private array $idAssignments = [];

	/** @var array<string, int> */
	private array $resolvedIds = [];

	private bool $idsDirty = false;

	public function __construct(
		private readonly Main $plugin
	){
		Settings::load($this->plugin->getConfig());
		EnchantTasks::init($this->plugin);
		$rarities = $this->plugin->getConfig()->get('rarities', []);
		Rarity::init(is_array($rarities) ? $rarities : []);
		$this->data = $this->loadData();
		$this->loadIdAssignments();

		foreach(DefaultEnchants::all() as $definition){
			$this->register($definition, null, $this->data[$definition->key] ?? []);
		}

		ItemRegistry::init();
		EnchantPlaceholders::register($this);
	}

	/**
	 * @param array<string, mixed> $config
	 * @return (Enchantment&CustomEnchantInterface)|null
	 * @throws InvalidArgumentException
	 */
	public function register(EnchantDefinition $definition, ?Plugin $owner = null, array $config = []) : ?Enchantment{
		$key = $definition->key;
		if(isset($this->definitions[$key])){
			$by = isset($this->owners[$key]) ? $this->owners[$key]->getName() : $this->plugin->getName();
			throw new InvalidArgumentException("Enchant '$key' is already registered by $by.");
		}

		$config = array_replace($this->defaultData($key), $definition->defaults, $config);
		$definition = $definition->withConfig($config);

		$this->definitions[$key] = $definition;
		$this->configs[$key] = $config;
		if($owner !== null){
			$this->owners[$key] = $owner;
		}
		$this->conflictGraph = null;

		if(($config['enabled'] ?? true) !== true){
			return null;
		}

		$source = $owner?->getName() ?? $this->plugin->getName();
		try{
			$enchant = ($definition->factory)(
				(string) ($config['display_name'] ?? $key),
				Rarity::fromName((string) ($config['rarity'] ?? 'COMMON'))->value,
				(string) ($config['description'] ?? ''),
				max(1, (int) ($config['max_level'] ?? 1)),
			);
			if(!$enchant instanceof Enchantment || !$enchant instanceof CustomEnchantInterface){
				throw new RuntimeException('factory must return an Enchantment implementing CustomEnchantInterface');
			}

			if(($config['has_chance'] ?? false) === true){
				$enchant->setBaseChance((float) ($config['chance'] ?? $config['base_chance'] ?? 0.1));
			}
			$enchant->setMaxChance((float) ($config['max_chance'] ?? Settings::maxLevelChance()));
			if(Rarity::get((string) ($config['rarity'] ?? 'COMMON')) === null){
				$this->plugin->getLogger()->warning("[$source] Enchant '$key' uses unknown rarity '{$config['rarity']}'; using " . Rarity::fromName('')->label() . '.');
			}

			$id = $this->resolveId($key, $definition->id);
			EnchantmentIdMap::getInstance()->register($id, $enchant);
		}catch(Throwable $exception){
			$this->plugin->getLogger()->error("[$source] Could not register enchant '$key': {$exception->getMessage()}");
			return null;
		}finally{
			$this->saveIdAssignments();
		}

		$this->resolvedIds[$key] = $id;
		$this->registered[$key] = $enchant;
		$this->keysByObject[spl_object_id($enchant)] = $key;

		return $enchant;
	}

	public function check(Item $item, string $key, int $level) : ApplyResult{
		$key = self::normalizeKey($key);
		$enchant = $this->registered[$key] ?? null;
		if($enchant === null){
			return ApplyResult::fail(ApplyStatus::UNKNOWN);
		}
		if(!$this->canApply($key, $item)){
			return ApplyResult::fail(ApplyStatus::INCOMPATIBLE_ITEM);
		}
		if($level > $enchant->getMaxLevel()){
			return ApplyResult::fail(ApplyStatus::LEVEL_TOO_HIGH, null, $enchant->getMaxLevel());
		}

		$definition = $this->definitions[$key];
		$present = $this->getKeysOnItem($item);
		$upgrading = isset($present[$key]);
		unset($present[$key]);

		$conflicts = $this->conflictGraph()[$key] ?? [];
		foreach($present as $other => $_level){
			if(isset($conflicts[$other])){
				return ApplyResult::fail(ApplyStatus::CONFLICT, $other);
			}
			if(in_array($key, $this->definitions[$other]->replaces ?? [], true)){
				return ApplyResult::fail(ApplyStatus::CONFLICT, $other);
			}
		}

		if(!$upgrading){
			foreach($definition->requires as $required => $minLevel){
				if(($present[$required] ?? 0) < $minLevel){
					return ApplyResult::fail(ApplyStatus::MISSING_REQUIREMENT, $required, $minLevel);
				}
			}
		}

		return ApplyResult::ok();
	}

	public function apply(Item $item, string $key, int $level, bool $force = false) : ApplyResult{
		$key = self::normalizeKey($key);
		$result = $this->check($item, $key, $level);

		if(!$result->isOk() && !($force && $result->status->isBypassable())){
			return $result;
		}

		foreach($this->definitions[$key]->replaces as $replaced){
			$old = $this->registered[$replaced] ?? null;
			if($old !== null && $item->hasEnchantment($old)){
				$item->removeEnchantment($old);
			}
		}

		$item->addEnchantment(new EnchantmentInstance($this->registered[$key], $level));
		$this->rebuildLore($item);

		return $result;
	}

	public function sendResult(Player $player, ApplyResult $result, string $key, int $level) : void{
		$key = self::normalizeKey($key);
		MessageService::send($player, $result->status->messageKey(), [
			'enchant_name' => $this->displayName($key),
			'level' => $level,
			'max_level' => $result->level,
			'other_name' => $result->other !== null ? $this->displayName($result->other) : '',
			'required_level' => $result->level,
		]);
	}

	/** @return array<string, int> */
	public function getKeysOnItem(Item $item) : array{
		$out = [];
		foreach($item->getEnchantments() as $instance){
			$key = $this->keyOf($instance->getType());
			if($key !== null){
				$out[$key] = $instance->getLevel();
			}
		}
		return $out;
	}

	/** @return list<array{0: string, 1: string}> */
	public function findConflictsOnItem(Item $item) : array{
		$keys = array_keys($this->getKeysOnItem($item));
		$graph = $this->conflictGraph();
		$pairs = [];
		foreach($keys as $i => $a){
			foreach(array_slice($keys, $i + 1) as $b){
				if(isset($graph[$a][$b])){
					$pairs[] = [$a, $b];
				}
			}
		}
		return $pairs;
	}

	/** @return array<string, array<string, string>> */
	private function conflictGraph() : array{
		if($this->conflictGraph !== null){
			return $this->conflictGraph;
		}

		$graph = [];
		foreach($this->definitions as $key => $definition){
			foreach($definition->conflicts as $other){
				if($other === $key){
					continue;
				}
				$graph[$key][$other] = $other;
				$graph[$other][$key] = $key;
			}
		}

		return $this->conflictGraph = $graph;
	}

	public static function normalizeKey(string $key) : string{
		return strtolower(str_replace([' ', '-', '_'], '', $key));
	}

	public function getByKey(string $key) : ?Enchantment{
		return $this->registered[self::normalizeKey($key)] ?? null;
	}

	public function keyOf(Enchantment $enchant) : ?string{
		return $this->keysByObject[spl_object_id($enchant)] ?? null;
	}

	public function getDefinition(string $key) : ?EnchantDefinition{
		return $this->definitions[self::normalizeKey($key)] ?? null;
	}

	/** @return array<string, EnchantDefinition> */
	public function getDefinitions() : array{
		return $this->definitions;
	}

	public function getOwner(string $key) : ?Plugin{
		return $this->owners[self::normalizeKey($key)] ?? null;
	}

	/** @return list<string> */
	public function getConflicts(string $key) : array{
		return array_values($this->conflictGraph()[self::normalizeKey($key)] ?? []);
	}

	/** @return list<array{0: string, 1: string}> */
	public function getConflictPairs() : array{
		$pairs = [];
		foreach($this->conflictGraph() as $a => $others){
			foreach($others as $b){
				if(strcmp($a, $b) < 0){
					$pairs[] = [$a, $b];
				}
			}
		}
		sort($pairs);
		return $pairs;
	}

	public function displayName(string $key) : string{
		$key = self::normalizeKey($key);
		$enchant = $this->registered[$key] ?? null;
		if($enchant !== null){
			$name = $enchant->getName();
			return $name instanceof Translatable ? $name->getText() : $name;
		}
		return (string) ($this->configs[$key]['display_name'] ?? $key);
	}

	public function getIdByKey(string $key) : ?int{
		$key = self::normalizeKey($key);

		return $this->resolvedIds[$key] ?? $this->definitions[$key]?->id;
	}

	/** @return array<string, Enchantment&CustomEnchantInterface> */
	public function getEnabled() : array{
		return $this->registered;
	}

	public function canApply(string $key, Item $item) : bool{
		$key = self::normalizeKey($key);
		$definition = $this->definitions[$key] ?? null;

		if($definition === null || !isset($this->registered[$key])){
			return false;
		}

		return ItemEnchantmentTagRegistry::getInstance()->isTagArrayIntersection(
			$definition->tags,
			$item->getEnchantmentTags()
		);
	}

	/** @return array<string, array<string, mixed>> */
	private function loadData() : array{
		$path = $this->plugin->getDataFolder() . 'enchantments.json';

		$contents = file_get_contents($path);
		if($contents === false){
			throw new RuntimeException('Could not read enchantments.json.');
		}

		$decoded = json_decode($contents, true);

		if(!is_array($decoded)){
			throw new RuntimeException(
				'Could not read enchantments.json: it must contain a JSON object.'
			);
		}

		$data = [];
		foreach($decoded as $key => $entry){
			if(is_string($key) && is_array($entry)){
				$data[self::normalizeKey($key)] = $entry;
			}
		}

		return $data;
	}

	/** @return array<string, mixed> */
	private function defaultData(string $key) : array{
		return [
			'enabled' => true,
			'display_name' => ucwords(str_replace('_', ' ', $key)),
			'description' => 'Configure this enchantment before enabling it.',
			'rarity' => 'COMMON',
			'max_level' => 1,
			'has_chance' => false,
			'chance' => 0.1
		];
	}

	private function resolveId(string $key, int $defaultId) : int{
		$id = $this->idAssignments[$key] ?? $defaultId;

		if(self::isSafeId($id) && EnchantmentIdMap::getInstance()->fromId($id) === null && !$this->isIdReservedByOther($id, $key)){
			return $id;
		}

		$taken = $id;
		$id = $this->nextFreeId();
		$this->idAssignments[$key] = $id;
		$this->nextId = max($this->nextId, $id + 1);
		$this->idsDirty = true;

		$reason = self::isSafeId($taken) ? 'already in use' : 'unsafe';
		$this->plugin->getLogger()->warning("Enchant ID $taken for '$key' is $reason; using $id instead.");

		return $id;
	}

	private function isIdReservedByOther(int $id, string $key) : bool{
		foreach($this->defaultIds() as $other => $defaultId){
			if($other !== $key && $defaultId === $id && !isset($this->idAssignments[$other])){
				return true;
			}
		}
		foreach($this->idAssignments as $other => $assigned){
			if($other !== $key && $assigned === $id){
				return true;
			}
		}
		return false;
	}

	/** @return array<string, int> */
	private function defaultIds() : array{
		$ids = [];
		foreach(DefaultEnchants::all() as $definition){
			$ids[$definition->key] = $definition->id;
		}
		foreach($this->definitions as $key => $definition){
			$ids[$key] = $definition->id;
		}
		return $ids;
	}

	public static function isSafeId(int $id) : bool{
		return $id >= 1 && $id <= self::MAX_ID && ($id & 0xFF) >= self::MIN_SAFE_LOW_BYTE;
	}

	private function nextFreeId() : int{
		$reserved = [
			...array_values($this->defaultIds()),
			...array_values($this->idAssignments),
		];
		$id = max(1021, $this->nextId);

		while(
			in_array($id, $reserved, true)
			|| EnchantmentIdMap::getInstance()->fromId($id) !== null
			|| !self::isSafeId($id)
		){
			if(++$id > self::MAX_ID){
				throw new RuntimeException('No enchantment IDs are available.');
			}
		}

		return $id;
	}

	private function loadIdAssignments() : void{
		$path = $this->plugin->getDataFolder() . 'enchant_ids.json';
		if(!is_file($path)){
			return;
		}

		try{
			$saved = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
		}catch(JsonException $exception){
			@copy($path, $path . '.bak');
			$this->plugin->getLogger()->warning("enchant_ids.json is unreadable ({$exception->getMessage()}); starting fresh (backup: enchant_ids.json.bak).");
			$this->idsDirty = true;
			return;
		}

		if(!is_array($saved)){
			$this->idsDirty = true;
			return;
		}

		$defaultIds = array_map(static fn(EnchantDefinition $d) : int => $d->id, DefaultEnchants::all());

		foreach($saved as $key => $id){
			$problem = match(true){
				!is_string($key) || $key === '' => 'has an invalid key',
				!is_int($id) || !self::isSafeId($id) => 'is not a safe ID',
				in_array($id, $defaultIds, true) || in_array($id, $this->idAssignments, true) => 'collides with another enchant',
				EnchantmentIdMap::getInstance()->fromId($id) !== null => 'is now used by another enchant',
				default => null
			};

			if($problem !== null){
				$this->plugin->getLogger()->warning("Saved enchant ID for '$key' $problem; assigning a new one.");
				$this->idsDirty = true;
				continue;
			}

			$this->idAssignments[$key] = $id;
		}

		if($this->idAssignments !== []){
			$this->nextId = max($this->nextId, max($this->idAssignments) + 1);
		}
	}

	private function saveIdAssignments() : void{
		if(!$this->idsDirty){
			return;
		}

		$directory = $this->plugin->getDataFolder();
		$path = $directory . 'enchant_ids.json';
		$temporary = $path . '.tmp';

		try{
			$json = json_encode($this->idAssignments, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
		}catch(JsonException $exception){
			$this->plugin->getLogger()->error("Could not encode enchant IDs: {$exception->getMessage()}");
			return;
		}

		if(file_put_contents($temporary, $json, LOCK_EX) === false || !rename($temporary, $path)){
			$this->plugin->getLogger()->error('Could not save enchant_ids.json; reassigned IDs may change next restart.');
			return;
		}

		$this->idsDirty = false;
	}

	public function rebuildLore(Item $item) : Item{
		if($item->getNamedTag()->getTag('hideEnchantments') !== null){
			return $item;
		}

		$oldGenerated = json_decode(
			$item->getNamedTag()->getString('custom_enchants_lore', '[]'),
			true
		);

		$oldGenerated = is_array($oldGenerated) ? $oldGenerated : [];

		$lore = array_values(array_filter(
			$item->getLore(),
			fn(string $line) : bool => !in_array($line, $oldGenerated, true)
		));

		$generated = [];

		foreach($item->getEnchantments() as $instance){
			$enchant = $instance->getType();

			if(!$enchant instanceof CustomEnchantInterface){
				continue;
			}

			$name = $enchant->getName();
			$name = $name instanceof Translatable ? $name->getText() : $name;

			$level = $instance->getLevel();

			$generated[] = $this->plugin->getMessages()->format(
				Settings::loreEntryFormat(),
				null,
				[
					'color' => Rarity::fromValue($enchant->getRarity())->color(),
					'name' => $name,
					'level' => Settings::loreRomanNumerals() ? self::intToRoman($level) : $level
				]
			);
		}

		if($generated !== []){
			sort($generated, SORT_NATURAL | SORT_FLAG_CASE);

			array_unshift(
				$generated,
				$this->plugin->getMessages()->format(Settings::loreHeader())
			);

			$lore = [...$lore, ...$generated];
		}

		$item->getNamedTag()->setString(
			'custom_enchants_lore',
			json_encode($generated, JSON_UNESCAPED_UNICODE)
		);

		$item->setLore($lore);

		return $item;
	}

	public static function intToRoman(int $number) : string{
		$map = [
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1
		];

		$result = '';

		foreach($map as $roman => $value){
			while($number >= $value){
				$result .= $roman;
				$number -= $value;
			}
		}

		return $result;
	}
}
