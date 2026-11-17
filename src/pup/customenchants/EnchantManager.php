<?php

namespace pup\customenchants;

use JsonException;
use RuntimeException;
use Throwable;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\{
	Armor,
	Axe,
	Bow,
	Hoe,
	Item,
	Pickaxe,
	Shovel,
	Sword,
	Tool,
};
use pocketmine\item\enchantment\{
	Enchantment,
	ItemFlags,
};
use pocketmine\lang\Translatable;
use pup\customenchants\enchants\armor\{
	AngelicEnchant,
	ArmoredEnchant,
	ArrowDeflectEnchant,
	CreeperArmorEnchant,
	CurseEnchant,
	DisappearEnchant,
	DivertEnchant,
	DodgeEnchant,
	FrozenEnchant,
	HeavyEnchant,
	LucidEnchant,
	MoltenEnchant,
	ObsidianshieldEnchant,
	OverloadEnchant,
	PoisonedEnchant,
	RagdollEnchant,
	ReflectEnchant,
	SafeguardEnchant,
	ShuffleEnchant,
	TankEnchant,
	TricksterEnchant,
	UnholyEnchant,
	VoodooEnchant,
	WitherEnchant,
};
use pup\customenchants\enchants\armor\boots\{
	ConvulseEnchant,
	GearsEnchant,
	JellyLegsEnchant,
	SpringsEnchant,
	WingsEnchant,
};
use pup\customenchants\enchants\armor\chestplate\{
	ChunkyEnchant,
	PhoenixEnchant,
	ShockwaveEnchant,
};
use pup\customenchants\enchants\armor\helmet\{
	AquaticEnchant,
	GlowingEnchant,
	ImplantsEnchant,
};
use pup\customenchants\enchants\armor\leggings\ExtinguishEnchant;
use pup\customenchants\enchants\axe\{
	BarbarianEnchant,
	BleedEnchant,
	CleaveEnchant,
	InflameEnchant,
	ReaperEnchant,
};
use pup\customenchants\enchants\bow\{
	ArcherEnchant,
	HellfireEnchant,
	PerishEnchant,
	PiercingEnchant,
	SnareEnchant,
	TeleportEnchant,
	VirusEnchant,
};
use pup\customenchants\enchants\durability\{
	AbidingEnchant,
	ImmortalEnchant,
	ReforgedEnchant,
	UnbreakableEnchant,
};
use pup\customenchants\enchants\sword\{
	AmbitEnchant,
	AronistEnchant,
	BerserkEnchant,
	BlindEnchant,
	ConfuseEnchant,
	CriticalEnchant,
	DecapitationEnchant,
	DevourEnchant,
	DisarmEnchant,
	DisarmorEnchant,
	DoubleStrikeEnchant,
	EpicnessEnchant,
	ExaltedEnchant,
	FamineEnchant,
	FeatherweightEnchant,
	ForcefieldEnchant,
	FuddleEnchant,
	IceAspectEnchant,
	ImmolationEnchant,
	InterruptEnchant,
	LaunchEnchant,
	LifestealEnchant,
	MagnetEnchant,
	ParalyzeEnchant,
	PermafrostEnchant,
	PoisonEnchant,
	RavenousEnchant,
	ReboundEnchant,
	VampireEnchant,
};
use pup\customenchants\enchants\tools\{
	ExperienceEnchant,
	HastenEnchant,
	RestoreEnchant,
	TelepathyEnchant,
};
use pup\customenchants\enchants\tools\axe\TimberEnchant;
use pup\customenchants\enchants\tools\hoe\{
	CarrotPlanterEnchant,
	PlanterEnchant,
	PotatoPlanterEnchant,
	ReplanterEnchant,
	SpeedEnchant,
};
use pup\customenchants\enchants\tools\pickaxe\{
	AutoSmeltEnchant,
	FeedEnchant,
	RebreatherEnchant,
	ReplenishEnchant,
	TrenchEnchant,
	VeinMinerEnchant,
};
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\types\SoulboundEnchant;
use pup\customenchants\utils\{
	EnchantPlaceholders,
	EnchantTasks,
	Rarity,
	Settings,
};

final class EnchantManager{

	/** @var array<string, array{id: int, flags: list<int>, make: \Closure}>|null */
	private static ?array $definitions = null;

	/**
	 * Internal enchantment definitions, built once.
	 *
	 * 'make' creates the enchant with a static `new`, so the DevTools shader can rewrite the class
	 * names. (A closure can't live in a const, hence a method instead of a constant.)
	 *
	 * @return array<string, array{id: int, flags: list<int>, make: \Closure(mixed ...): (Enchantment&CustomEnchantInterface)}>
	 */
	private static function definitions() : array{
		return self::$definitions ??= [
			'feed' => ['id' => 1000, 'flags' => [ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new FeedEnchant(...$a)],
			'speed' => ['id' => 1003, 'flags' => [ItemFlags::HOE, ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new SpeedEnchant(...$a)],
			'blind' => ['id' => 1006, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new BlindEnchant(...$a)],
			'aronist' => ['id' => 1007, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new AronistEnchant(...$a)],
			'teleport' => ['id' => 1008, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new TeleportEnchant(...$a)],
			'overload' => ['id' => 1009, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new OverloadEnchant(...$a)],
			'glowing' => ['id' => 1010, 'flags' => [ItemFlags::HEAD], 'make' => static fn(mixed ...$a) => new GlowingEnchant(...$a)],
			'gears' => ['id' => 1011, 'flags' => [ItemFlags::FEET], 'make' => static fn(mixed ...$a) => new GearsEnchant(...$a)],
			'restore' => ['id' => 1013, 'flags' => [ItemFlags::DIG], 'make' => static fn(mixed ...$a) => new RestoreEnchant(...$a)],
			'shuffle' => ['id' => 1014, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ShuffleEnchant(...$a)],
			'aquatic' => ['id' => 1016, 'flags' => [ItemFlags::HEAD], 'make' => static fn(mixed ...$a) => new AquaticEnchant(...$a)],
			'autosmelt' => ['id' => 1017, 'flags' => [ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new AutoSmeltEnchant(...$a)],
			'experience' => ['id' => 1018, 'flags' => [ItemFlags::DIG], 'make' => static fn(mixed ...$a) => new ExperienceEnchant(...$a)],
			'poison' => ['id' => 1019, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new PoisonEnchant(...$a)],
			'confuse' => ['id' => 1020, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new ConfuseEnchant(...$a)],
			'iceaspect' => ['id' => 1021, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new IceAspectEnchant(...$a)],
			'famine' => ['id' => 1022, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new FamineEnchant(...$a)],
			'interrupt' => ['id' => 1023, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new InterruptEnchant(...$a)],
			'reaper' => ['id' => 1024, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new ReaperEnchant(...$a)],
			'paralyze' => ['id' => 1025, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new ParalyzeEnchant(...$a)],
			'berserk' => ['id' => 1026, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new BerserkEnchant(...$a)],
			'featherweight' => ['id' => 1027, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new FeatherweightEnchant(...$a)],
			'lifesteal' => ['id' => 1028, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new LifestealEnchant(...$a)],
			'rebound' => ['id' => 1029, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new ReboundEnchant(...$a)],
			'ravenous' => ['id' => 1030, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new RavenousEnchant(...$a)],
			'devour' => ['id' => 1031, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new DevourEnchant(...$a)],
			'doublestrike' => ['id' => 1032, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new DoubleStrikeEnchant(...$a)],
			'critical' => ['id' => 1033, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new CriticalEnchant(...$a)],
			'barbarian' => ['id' => 1034, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new BarbarianEnchant(...$a)],
			'magnet' => ['id' => 1035, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new MagnetEnchant(...$a)],
			'forcefield' => ['id' => 1036, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new ForcefieldEnchant(...$a)],
			'disarm' => ['id' => 1037, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new DisarmEnchant(...$a)],
			'disarmor' => ['id' => 1038, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new DisarmorEnchant(...$a)],
			'fuddle' => ['id' => 1039, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new FuddleEnchant(...$a)],
			'decapitation' => ['id' => 1040, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new DecapitationEnchant(...$a)],
			'exalted' => ['id' => 1041, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new ExaltedEnchant(...$a)],
			'epicness' => ['id' => 1042, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new EpicnessEnchant(...$a)],
			'hasten' => ['id' => 1043, 'flags' => [ItemFlags::DIG], 'make' => static fn(mixed ...$a) => new HastenEnchant(...$a)],
			'telepathy' => ['id' => 1044, 'flags' => [ItemFlags::DIG], 'make' => static fn(mixed ...$a) => new TelepathyEnchant(...$a)],
			'trench' => ['id' => 1045, 'flags' => [ItemFlags::PICKAXE, ItemFlags::SHOVEL], 'make' => static fn(mixed ...$a) => new TrenchEnchant(...$a)],
			'veinminer' => ['id' => 1046, 'flags' => [ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new VeinMinerEnchant(...$a)],
			'timber' => ['id' => 1047, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new TimberEnchant(...$a)],
			'replenish' => ['id' => 1048, 'flags' => [ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new ReplenishEnchant(...$a)],
			'hellfire' => ['id' => 1050, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new HellfireEnchant(...$a)],
			'piercing' => ['id' => 1051, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new PiercingEnchant(...$a)],
			'archer' => ['id' => 1052, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new ArcherEnchant(...$a)],
			'snare' => ['id' => 1053, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new SnareEnchant(...$a)],
			'perish' => ['id' => 1054, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new PerishEnchant(...$a)],
			'virus' => ['id' => 1055, 'flags' => [ItemFlags::BOW], 'make' => static fn(mixed ...$a) => new VirusEnchant(...$a)],
			'poisoned' => ['id' => 1056, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new PoisonedEnchant(...$a)],
			'frozen' => ['id' => 1057, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new FrozenEnchant(...$a)],
			'wither' => ['id' => 1058, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new WitherEnchant(...$a)],
			'voodoo' => ['id' => 1059, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new VoodooEnchant(...$a)],
			'curse' => ['id' => 1060, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new CurseEnchant(...$a)],
			'unholy' => ['id' => 1061, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new UnholyEnchant(...$a)],
			'divert' => ['id' => 1062, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new DivertEnchant(...$a)],
			'angelic' => ['id' => 1063, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new AngelicEnchant(...$a)],
			'molten' => ['id' => 1064, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new MoltenEnchant(...$a)],
			'extinguish' => ['id' => 1065, 'flags' => [ItemFlags::LEGS], 'make' => static fn(mixed ...$a) => new ExtinguishEnchant(...$a)],
			'springs' => ['id' => 1066, 'flags' => [ItemFlags::FEET], 'make' => static fn(mixed ...$a) => new SpringsEnchant(...$a)],
			'obsidianshield' => ['id' => 1067, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ObsidianshieldEnchant(...$a)],
			'armored' => ['id' => 1068, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ArmoredEnchant(...$a)],
			'heavy' => ['id' => 1069, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new HeavyEnchant(...$a)],
			'tank' => ['id' => 1070, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new TankEnchant(...$a)],
			'dodge' => ['id' => 1071, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new DodgeEnchant(...$a)],
			'arrowdeflect' => ['id' => 1072, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ArrowDeflectEnchant(...$a)],
			'creeperarmor' => ['id' => 1073, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new CreeperArmorEnchant(...$a)],
			'jellylegs' => ['id' => 1074, 'flags' => [ItemFlags::FEET], 'make' => static fn(mixed ...$a) => new JellyLegsEnchant(...$a)],
			'chunky' => ['id' => 1075, 'flags' => [ItemFlags::TORSO], 'make' => static fn(mixed ...$a) => new ChunkyEnchant(...$a)],
			'reflect' => ['id' => 1076, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ReflectEnchant(...$a)],
			'phoenix' => ['id' => 1077, 'flags' => [ItemFlags::TORSO], 'make' => static fn(mixed ...$a) => new PhoenixEnchant(...$a)],
			'vampire' => ['id' => 1078, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new VampireEnchant(...$a)],
			'bleed' => ['id' => 1079, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new BleedEnchant(...$a)],
			'permafrost' => ['id' => 1080, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new PermafrostEnchant(...$a)],
			'ambit' => ['id' => 1081, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new AmbitEnchant(...$a)],
			'cleave' => ['id' => 1082, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new CleaveEnchant(...$a)],
			'immolation' => ['id' => 1083, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new ImmolationEnchant(...$a)],
			'inflame' => ['id' => 1084, 'flags' => [ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new InflameEnchant(...$a)],
			'replanter' => ['id' => 1085, 'flags' => [ItemFlags::HOE], 'make' => static fn(mixed ...$a) => new ReplanterEnchant(...$a)],
			'rebreather' => ['id' => 1086, 'flags' => [ItemFlags::PICKAXE], 'make' => static fn(mixed ...$a) => new RebreatherEnchant(...$a)],
			'planter' => ['id' => 1087, 'flags' => [ItemFlags::HOE], 'make' => static fn(mixed ...$a) => new PlanterEnchant(...$a)],
			'carrotplanter' => ['id' => 1088, 'flags' => [ItemFlags::HOE], 'make' => static fn(mixed ...$a) => new CarrotPlanterEnchant(...$a)],
			'potatoplanter' => ['id' => 1089, 'flags' => [ItemFlags::HOE], 'make' => static fn(mixed ...$a) => new PotatoPlanterEnchant(...$a)],
			'launch' => ['id' => 1090, 'flags' => [ItemFlags::SWORD], 'make' => static fn(mixed ...$a) => new LaunchEnchant(...$a)],
			'unbreakable' => ['id' => 1091, 'flags' => [ItemFlags::DIG, ItemFlags::TOOL], 'make' => static fn(mixed ...$a) => new UnbreakableEnchant(...$a)],
			'abiding' => ['id' => 1092, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE], 'make' => static fn(mixed ...$a) => new AbidingEnchant(...$a)],
			'reforged' => ['id' => 1093, 'flags' => [ItemFlags::SWORD, ItemFlags::DIG], 'make' => static fn(mixed ...$a) => new ReforgedEnchant(...$a)],
			'immortal' => ['id' => 1094, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new ImmortalEnchant(...$a)],
			'soulbound' => ['id' => 1095, 'flags' => [ItemFlags::ALL], 'make' => static fn(mixed ...$a) => new SoulboundEnchant(...$a)],
			'implants' => ['id' => 1096, 'flags' => [ItemFlags::HEAD], 'make' => static fn(mixed ...$a) => new ImplantsEnchant(...$a)],
			'wings' => ['id' => 1097, 'flags' => [ItemFlags::FEET], 'make' => static fn(mixed ...$a) => new WingsEnchant(...$a)],
			'trickster' => ['id' => 1098, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new TricksterEnchant(...$a)],
			'ragdoll' => ['id' => 1099, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new RagdollEnchant(...$a)],
			'convulse' => ['id' => 1100, 'flags' => [ItemFlags::FEET], 'make' => static fn(mixed ...$a) => new ConvulseEnchant(...$a)],
			'safeguard' => ['id' => 1101, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new SafeguardEnchant(...$a)],
			'lucid' => ['id' => 1102, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new LucidEnchant(...$a)],
			'disappear' => ['id' => 1103, 'flags' => [ItemFlags::ARMOR], 'make' => static fn(mixed ...$a) => new DisappearEnchant(...$a)],
			'shockwave' => ['id' => 1104, 'flags' => [ItemFlags::TORSO], 'make' => static fn(mixed ...$a) => new ShockwaveEnchant(...$a)]
		];
	}

	public const int MIN_SAFE_LOW_BYTE = 64;
	public const int MAX_ID = 32767;

	/** @var array<string, array<string, mixed>> */
	private array $data;

	/** Next ID to try when a definition's own ID can't be used. */
	private int $nextId = 1021;

	/** @var array<string, int>*/
	private array $idAssignments = [];

	/** @var array<string, int>*/
	private array $resolvedIds = [];

	private bool $idsDirty = false;

	/** @var array<string, array{color: string, weight: int}> */
	private const array RARITY_DEFAULTS = [
		'COMMON' => ['color' => '{gray}', 'weight' => 50],
		'UNCOMMON' => ['color' => '{green}', 'weight' => 25],
		'RARE' => ['color' => '{blue}', 'weight' => 15],
		'EPIC' => ['color' => '{light_purple}', 'weight' => 10],
		'LEGENDARY' => ['color' => '{gold}', 'weight' => 5],
		'MASTERY' => ['color' => '{red}', 'weight' => 3],
		'HEROIC' => ['color' => '{dark_red}', 'weight' => 0],
	];

	/** @var array<string, array{color: string, weight: int, enabled: bool}> */
	private static array $raritySettings = [];

	/** @var array<string, Enchantment> */
	private array $registered = [];

	public function __construct(
		private readonly Main $plugin
	){
		Settings::load($this->plugin->getConfig());
		EnchantTasks::init($this->plugin);
		$this->loadRarities();
		$this->data = $this->loadData();
		$this->loadIdAssignments();

		$this->registerEnchants();

		ItemRegistry::init();
		EnchantPlaceholders::register($this);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
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

		foreach(self::definitions() as $key => $_definition){
			if(!isset($decoded[$key]) || !is_array($decoded[$key])){
				$decoded[$key] = $this->defaultData($key);
			}
		}

		return $decoded;
	}

	/**
	 * @return array<string, mixed>
	 */
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

	/**
	 * @param array<string, mixed> $data
	 * @param list<int> $flags
	 */
	private function createEnchantment(
		string $key,
		array $data,
		array $flags
	) : Enchantment|CustomEnchantInterface {
		$arguments = [
			(string) ($data['display_name'] ?? $key),
			Rarity::fromName((string) ($data['rarity'] ?? 'COMMON'))->value,
			(string) ($data['description'] ?? ''),
			max(1, (int) ($data['max_level'] ?? 1)),
			...$flags
		];

		$make = self::definitions()[$key]['make'] ?? throw new RuntimeException("Unknown enchantment: $key");

		return $make(...$arguments);
	}

	/**
	 * Register enabled enchantments.
	 */
	private function registerEnchants() : void{
		foreach(self::definitions() as $key => $definition){
			$data = $this->data[$key];

			if(($data['enabled'] ?? true) !== true){
				continue;
			}

			try{
				$enchant = $this->createEnchantment(
					$key,
					$data,
					$definition['flags']
				);

				if(($data['has_chance'] ?? false) === true){
					$enchant->setBaseChance(
						(float) ($data['chance'] ?? $data['base_chance'] ?? 0.1)
					);
				}

				$id = $this->resolveId($key, $definition['id']);

				EnchantmentIdMap::getInstance()->register($id, $enchant);

				$this->resolvedIds[$key] = $id;
				$this->registered[$key] = $enchant;
			}catch(Throwable $exception){
				$this->plugin->getLogger()->error(
					"Could not register $key: {$exception->getMessage()}"
				);
			}
		}

		$this->saveIdAssignments();
	}

	private function resolveId(string $key, int $defaultId) : int{
		$id = $this->idAssignments[$key] ?? $defaultId;

		if(self::isSafeId($id) && EnchantmentIdMap::getInstance()->fromId($id) === null){
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

	public static function isSafeId(int $id) : bool{
		return $id >= 1 && $id <= self::MAX_ID && ($id & 0xFF) >= self::MIN_SAFE_LOW_BYTE;
	}

	private function nextFreeId() : int{
		$reserved = [...array_column(self::definitions(), 'id'), ...array_values($this->idAssignments)];
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

		$defaultIds = array_column(self::definitions(), 'id');

		foreach($saved as $key => $id){
			$problem = match(true){
				!is_string($key) || !isset(self::definitions()[$key]) => 'is not a known enchant any more',
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

	private function loadRarities() : void{
		$config = $this->plugin->getConfig()->get('rarities', []);
		$config = is_array($config) ? $config : [];

		self::$raritySettings = [];
		foreach(Rarity::cases() as $rarity){
			$section = $config[$rarity->name] ?? [];
			$section = is_array($section) ? $section : [];
			$defaults = self::RARITY_DEFAULTS[$rarity->name];

			self::$raritySettings[$rarity->name] = [
				'color' => (string) ($section['display_color'] ?? $defaults['color']),
				'weight' => (int) ($section['weight'] ?? $defaults['weight']),
				'enabled' => (bool) ($section['enabled'] ?? true),
			];
		}
	}

	public static function rarityColor(Rarity $rarity) : string{
		return self::$raritySettings[$rarity->name]['color'] ?? self::RARITY_DEFAULTS[$rarity->name]['color'];
	}

	public static function rarityWeight(Rarity $rarity) : int{
		return self::$raritySettings[$rarity->name]['weight'] ?? self::RARITY_DEFAULTS[$rarity->name]['weight'];
	}

	public static function isRarityEnabled(Rarity $rarity) : bool{
		return self::$raritySettings[$rarity->name]['enabled'] ?? true;
	}

	/**
	 * Normalize an internal enchantment key.
	 */
	public static function normalizeKey(string $key) : string{
		return strtolower(str_replace([' ', '-'], '', $key));
	}

	/**
	 * Get an enchantment by its permanent internal key.
	 */
	public function getByKey(string $key) : ?Enchantment{
		return $this->registered[self::normalizeKey($key)] ?? null;
	}

	/**
	 * Get the numeric ID an enchantment is actually registered under (which differs from definitions()
	 * when the default ID was unsafe or taken).
	 */
	public function getIdByKey(string $key) : ?int{
		$key = self::normalizeKey($key);

		return $this->resolvedIds[$key] ?? self::definitions()[$key]['id'] ?? null;
	}

	/**
	 * Get all enabled enchantments.
	 *
	 * @return array<string, Enchantment>
	 */
	public function getEnabled() : array{
		return $this->registered;
	}

	/**
	 * Check whether an enchantment can be applied to an item.
	 */
	public function canApply(string $key, Item $item) : bool{
		$key = self::normalizeKey($key);

		$definition = self::definitions()[$key] ?? null;

		if($definition === null || $this->getByKey($key) === null){
			return false;
		}

		$itemFlags = self::getItemFlags($item);

		foreach($definition['flags'] as $flag){
			if(($itemFlags & $flag) !== 0){
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine an item's enchantment flags.
	 */
	private static function getItemFlags(Item $item) : int{
		return match(true){
			$item instanceof Sword => ItemFlags::SWORD,
			$item instanceof Bow => ItemFlags::BOW,
			$item instanceof Pickaxe => ItemFlags::PICKAXE | ItemFlags::DIG | ItemFlags::TOOL,
			$item instanceof Axe => ItemFlags::AXE | ItemFlags::DIG | ItemFlags::TOOL,
			$item instanceof Shovel => ItemFlags::SHOVEL | ItemFlags::DIG | ItemFlags::TOOL,
			$item instanceof Hoe => ItemFlags::HOE | ItemFlags::DIG | ItemFlags::TOOL,
			$item instanceof Tool => ItemFlags::DIG | ItemFlags::TOOL,
			$item instanceof Armor => ItemFlags::ARMOR | match($item->getArmorSlot()){
					ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
					ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
					ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
					ArmorInventory::SLOT_FEET => ItemFlags::FEET,
					default => ItemFlags::NONE
				},
			default => ItemFlags::NONE
		};
	}

	/**
	 * Rebuild custom enchantment lore.
	 */
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

	/**
	 * Convert an integer to Roman numerals.
	 */
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