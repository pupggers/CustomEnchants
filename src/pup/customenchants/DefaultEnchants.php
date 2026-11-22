<?php

declare(strict_types=1);

namespace pup\customenchants;

use pocketmine\item\enchantment\ItemEnchantmentTags;
use pup\customenchants\api\EnchantDefinition;
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
use pup\customenchants\types\SoulboundEnchant;

final class DefaultEnchants
{
	public const array CONFLICT_GROUPS = [
		['planter', 'carrotplanter', 'potatoplanter'],
		['forcefield', 'magnet'],
		['iceaspect', 'permafrost'],
		['berserk', 'featherweight'],
		['abiding', 'reforged'],
		['abiding', 'unbreakable'],
		['abiding', 'restore'],
		['unbreakable', 'reforged'],
		['unbreakable', 'restore'],
		['trench', 'veinminer'],
		['glowing', 'lucid'],
		['obsidianshield', 'extinguish'],
	];

	/** @var array<string, array{id: int, tags: list<string>, make: \Closure}>|null */
	private static ?array $definitions = null;

	/**
	 * 'make' uses a static `new` so the DevTools shader can rewrite class names.
	 *
	 * @return array<string, array{id: int, tags: list<string>, make: \Closure}>
	 */
	private static function raw(): array
	{
		return self::$definitions ??= [
			'feed' => ['id' => 1000, 'tags' => [ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new FeedEnchant(...$a)],
			'speed' => ['id' => 1003, 'tags' => [ItemEnchantmentTags::HOE, ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new SpeedEnchant(...$a)],
			'blind' => ['id' => 1006, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new BlindEnchant(...$a)],
			'aronist' => ['id' => 1007, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new AronistEnchant(...$a)],
			'teleport' => ['id' => 1008, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new TeleportEnchant(...$a)],
			'overload' => ['id' => 1009, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new OverloadEnchant(...$a)],
			'glowing' => ['id' => 1010, 'tags' => [ItemEnchantmentTags::HELMET], 'make' => static fn(mixed ...$a) => new GlowingEnchant(...$a)],
			'gears' => ['id' => 1011, 'tags' => [ItemEnchantmentTags::BOOTS], 'make' => static fn(mixed ...$a) => new GearsEnchant(...$a)],
			'restore' => ['id' => 1013, 'tags' => [ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new RestoreEnchant(...$a)],
			'shuffle' => ['id' => 1014, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ShuffleEnchant(...$a)],
			'aquatic' => ['id' => 1016, 'tags' => [ItemEnchantmentTags::HELMET], 'make' => static fn(mixed ...$a) => new AquaticEnchant(...$a)],
			'autosmelt' => ['id' => 1017, 'tags' => [ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new AutoSmeltEnchant(...$a)],
			'experience' => ['id' => 1018, 'tags' => [ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new ExperienceEnchant(...$a)],
			'poison' => ['id' => 1019, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new PoisonEnchant(...$a)],
			'confuse' => ['id' => 1020, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new ConfuseEnchant(...$a)],
			'iceaspect' => ['id' => 1021, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new IceAspectEnchant(...$a)],
			'famine' => ['id' => 1022, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new FamineEnchant(...$a)],
			'interrupt' => ['id' => 1023, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new InterruptEnchant(...$a)],
			'reaper' => ['id' => 1024, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new ReaperEnchant(...$a)],
			'paralyze' => ['id' => 1025, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new ParalyzeEnchant(...$a)],
			'berserk' => ['id' => 1026, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new BerserkEnchant(...$a)],
			'featherweight' => ['id' => 1027, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new FeatherweightEnchant(...$a)],
			'lifesteal' => ['id' => 1028, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new LifestealEnchant(...$a)],
			'rebound' => ['id' => 1029, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new ReboundEnchant(...$a)],
			'ravenous' => ['id' => 1030, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new RavenousEnchant(...$a)],
			'devour' => ['id' => 1031, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new DevourEnchant(...$a)],
			'doublestrike' => ['id' => 1032, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new DoubleStrikeEnchant(...$a)],
			'critical' => ['id' => 1033, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new CriticalEnchant(...$a)],
			'barbarian' => ['id' => 1034, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new BarbarianEnchant(...$a)],
			'magnet' => ['id' => 1035, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new MagnetEnchant(...$a)],
			'forcefield' => ['id' => 1036, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new ForcefieldEnchant(...$a)],
			'disarm' => ['id' => 1037, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new DisarmEnchant(...$a)],
			'disarmor' => ['id' => 1038, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new DisarmorEnchant(...$a)],
			'fuddle' => ['id' => 1039, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new FuddleEnchant(...$a)],
			'decapitation' => ['id' => 1040, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new DecapitationEnchant(...$a)],
			'exalted' => ['id' => 1041, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new ExaltedEnchant(...$a)],
			'epicness' => ['id' => 1042, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new EpicnessEnchant(...$a)],
			'hasten' => ['id' => 1043, 'tags' => [ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new HastenEnchant(...$a)],
			'telepathy' => ['id' => 1044, 'tags' => [ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new TelepathyEnchant(...$a)],
			'trench' => ['id' => 1045, 'tags' => [ItemEnchantmentTags::PICKAXE, ItemEnchantmentTags::SHOVEL], 'make' => static fn(mixed ...$a) => new TrenchEnchant(...$a)],
			'veinminer' => ['id' => 1046, 'tags' => [ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new VeinMinerEnchant(...$a)],
			'timber' => ['id' => 1047, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new TimberEnchant(...$a)],
			'replenish' => ['id' => 1048, 'tags' => [ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new ReplenishEnchant(...$a)],
			'hellfire' => ['id' => 1050, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new HellfireEnchant(...$a)],
			'piercing' => ['id' => 1051, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new PiercingEnchant(...$a)],
			'archer' => ['id' => 1052, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new ArcherEnchant(...$a)],
			'snare' => ['id' => 1053, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new SnareEnchant(...$a)],
			'perish' => ['id' => 1054, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new PerishEnchant(...$a)],
			'virus' => ['id' => 1055, 'tags' => [ItemEnchantmentTags::BOW], 'make' => static fn(mixed ...$a) => new VirusEnchant(...$a)],
			'poisoned' => ['id' => 1056, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new PoisonedEnchant(...$a)],
			'frozen' => ['id' => 1057, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new FrozenEnchant(...$a)],
			'wither' => ['id' => 1058, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new WitherEnchant(...$a)],
			'voodoo' => ['id' => 1059, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new VoodooEnchant(...$a)],
			'curse' => ['id' => 1060, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new CurseEnchant(...$a)],
			'unholy' => ['id' => 1061, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new UnholyEnchant(...$a)],
			'divert' => ['id' => 1062, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new DivertEnchant(...$a)],
			'angelic' => ['id' => 1063, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new AngelicEnchant(...$a)],
			'molten' => ['id' => 1064, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new MoltenEnchant(...$a)],
			'extinguish' => ['id' => 1065, 'tags' => [ItemEnchantmentTags::LEGGINGS], 'make' => static fn(mixed ...$a) => new ExtinguishEnchant(...$a)],
			'springs' => ['id' => 1066, 'tags' => [ItemEnchantmentTags::BOOTS], 'make' => static fn(mixed ...$a) => new SpringsEnchant(...$a)],
			'obsidianshield' => ['id' => 1067, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ObsidianshieldEnchant(...$a)],
			'armored' => ['id' => 1068, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ArmoredEnchant(...$a)],
			'heavy' => ['id' => 1069, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new HeavyEnchant(...$a)],
			'tank' => ['id' => 1070, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new TankEnchant(...$a)],
			'dodge' => ['id' => 1071, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new DodgeEnchant(...$a)],
			'arrowdeflect' => ['id' => 1072, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ArrowDeflectEnchant(...$a)],
			'creeperarmor' => ['id' => 1073, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new CreeperArmorEnchant(...$a)],
			'jellylegs' => ['id' => 1074, 'tags' => [ItemEnchantmentTags::BOOTS], 'make' => static fn(mixed ...$a) => new JellyLegsEnchant(...$a)],
			'chunky' => ['id' => 1075, 'tags' => [ItemEnchantmentTags::CHESTPLATE], 'make' => static fn(mixed ...$a) => new ChunkyEnchant(...$a)],
			'reflect' => ['id' => 1076, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ReflectEnchant(...$a)],
			'phoenix' => ['id' => 1077, 'tags' => [ItemEnchantmentTags::CHESTPLATE], 'make' => static fn(mixed ...$a) => new PhoenixEnchant(...$a)],
			'vampire' => ['id' => 1078, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new VampireEnchant(...$a)],
			'bleed' => ['id' => 1079, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new BleedEnchant(...$a)],
			'permafrost' => ['id' => 1080, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new PermafrostEnchant(...$a)],
			'ambit' => ['id' => 1081, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new AmbitEnchant(...$a)],
			'cleave' => ['id' => 1082, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new CleaveEnchant(...$a)],
			'immolation' => ['id' => 1083, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new ImmolationEnchant(...$a)],
			'inflame' => ['id' => 1084, 'tags' => [ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new InflameEnchant(...$a)],
			'replanter' => ['id' => 1085, 'tags' => [ItemEnchantmentTags::HOE], 'make' => static fn(mixed ...$a) => new ReplanterEnchant(...$a)],
			'rebreather' => ['id' => 1086, 'tags' => [ItemEnchantmentTags::PICKAXE], 'make' => static fn(mixed ...$a) => new RebreatherEnchant(...$a)],
			'planter' => ['id' => 1087, 'tags' => [ItemEnchantmentTags::HOE], 'make' => static fn(mixed ...$a) => new PlanterEnchant(...$a)],
			'carrotplanter' => ['id' => 1088, 'tags' => [ItemEnchantmentTags::HOE], 'make' => static fn(mixed ...$a) => new CarrotPlanterEnchant(...$a)],
			'potatoplanter' => ['id' => 1089, 'tags' => [ItemEnchantmentTags::HOE], 'make' => static fn(mixed ...$a) => new PotatoPlanterEnchant(...$a)],
			'launch' => ['id' => 1090, 'tags' => [ItemEnchantmentTags::SWORD], 'make' => static fn(mixed ...$a) => new LaunchEnchant(...$a)],
			'unbreakable' => ['id' => 1091, 'tags' => [ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new UnbreakableEnchant(...$a)],
			'abiding' => ['id' => 1092, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::AXE], 'make' => static fn(mixed ...$a) => new AbidingEnchant(...$a)],
			'reforged' => ['id' => 1093, 'tags' => [ItemEnchantmentTags::SWORD, ItemEnchantmentTags::BLOCK_TOOLS], 'make' => static fn(mixed ...$a) => new ReforgedEnchant(...$a)],
			'immortal' => ['id' => 1094, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new ImmortalEnchant(...$a)],
			'soulbound' => ['id' => 1095, 'tags' => [ItemEnchantmentTags::ALL], 'make' => static fn(mixed ...$a) => new SoulboundEnchant(...$a)],
			'implants' => ['id' => 1096, 'tags' => [ItemEnchantmentTags::HELMET], 'make' => static fn(mixed ...$a) => new ImplantsEnchant(...$a)],
			'wings' => ['id' => 1097, 'tags' => [ItemEnchantmentTags::BOOTS], 'make' => static fn(mixed ...$a) => new WingsEnchant(...$a)],
			'trickster' => ['id' => 1098, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new TricksterEnchant(...$a)],
			'ragdoll' => ['id' => 1099, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new RagdollEnchant(...$a)],
			'convulse' => ['id' => 1100, 'tags' => [ItemEnchantmentTags::BOOTS], 'make' => static fn(mixed ...$a) => new ConvulseEnchant(...$a)],
			'safeguard' => ['id' => 1101, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new SafeguardEnchant(...$a)],
			'lucid' => ['id' => 1102, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new LucidEnchant(...$a)],
			'disappear' => ['id' => 1103, 'tags' => [ItemEnchantmentTags::ARMOR], 'make' => static fn(mixed ...$a) => new DisappearEnchant(...$a)],
			'shockwave' => ['id' => 1104, 'tags' => [ItemEnchantmentTags::CHESTPLATE], 'make' => static fn(mixed ...$a) => new ShockwaveEnchant(...$a)]
		];
	}

	/** @var list<EnchantDefinition>|null */
	private static ?array $all = null;

	/** @return list<EnchantDefinition> */
	public static function all(): array
	{
		return self::$all ??= self::build();
	}

	/** @return list<EnchantDefinition> */
	private static function build(): array
	{
		$conflicts = [];
		foreach (self::CONFLICT_GROUPS as $group) {
			foreach ($group as $a) {
				foreach ($group as $b) {
					if ($a !== $b) {
						$conflicts[$a][$b] = $b;
					}
				}
			}
		}

		$out = [];
		foreach (self::raw() as $key => $def) {
			$out[] = new EnchantDefinition(
				$key,
				$def['id'],
				$def['tags'],
				$def['make'],
				array_values($conflicts[$key] ?? []),
			);
		}
		return $out;
	}
}
