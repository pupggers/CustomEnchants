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
	//TODO: A better way for this.
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

	/**
	 * @var array<string, array<string, mixed>>
	 */
	private const array DEFAULTS = [
		'feed' => ['enabled' => true, 'display_name' => 'Feed', 'description' => 'Chance to restore food while mining.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'speed' => ['enabled' => true, 'display_name' => 'Speed', 'description' => 'Gives you speed after breaking blocks.', 'rarity' => 'UNCOMMON', 'max_level' => 2, 'has_chance' => false],
		'blind' => ['enabled' => true, 'display_name' => 'Blind', 'description' => 'Chance to blind your opponent on hit.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'aronist' => ['enabled' => true, 'display_name' => 'Aronist', 'description' => 'Chance to deal extra damage while you are on fire.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'teleport' => ['enabled' => true, 'display_name' => 'Teleport', 'description' => 'Teleports you to where your arrow lands.', 'rarity' => 'RARE', 'max_level' => 1, 'has_chance' => false],
		'overload' => ['enabled' => true, 'display_name' => 'Overload', 'description' => 'Increases your maximum health while worn.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => false],
		'glowing' => ['enabled' => true, 'display_name' => 'Glowing', 'description' => 'Gives you night vision while worn.', 'rarity' => 'COMMON', 'max_level' => 1, 'has_chance' => false],
		'gears' => ['enabled' => true, 'display_name' => 'Gears', 'description' => 'Increases your movement speed while worn.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => false],
		'restore' => ['enabled' => true, 'display_name' => 'Restore', 'description' => 'Chance to repair your tool as you mine.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'shuffle' => ['enabled' => true, 'display_name' => 'Shuffle', 'description' => 'Chance to shuffle things up when you are hit.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'aquatic' => ['enabled' => true, 'display_name' => 'Aquatic', 'description' => 'Lets you breathe underwater while worn.', 'rarity' => 'COMMON', 'max_level' => 1, 'has_chance' => false],
		'autosmelt' => ['enabled' => true, 'display_name' => 'Auto Smelt', 'description' => 'Chance to smelt the blocks you mine.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'experience' => ['enabled' => true, 'display_name' => 'Experience', 'description' => 'Chance to gain extra XP when mining.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'poison' => ['enabled' => true, 'display_name' => 'Poison', 'description' => 'Chance to poison the target.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'confuse' => ['enabled' => true, 'display_name' => 'Confuse', 'description' => 'Chance to give the target nausea.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'iceaspect' => ['enabled' => true, 'display_name' => 'Ice Aspect', 'description' => 'Chance to slow the target.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'famine' => ['enabled' => true, 'display_name' => 'Famine', 'description' => 'Chance to give the target hunger.', 'rarity' => 'UNCOMMON', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.15],
		'interrupt' => ['enabled' => true, 'display_name' => 'Interrupt', 'description' => 'Chance to give the target mining fatigue.', 'rarity' => 'EPIC', 'max_level' => 2, 'has_chance' => true, 'chance' => 0.1],
		'reaper' => ['enabled' => true, 'display_name' => 'Reaper', 'description' => 'Chance to wither and blind the target.', 'rarity' => 'RARE', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'paralyze' => ['enabled' => true, 'display_name' => 'Paralyze', 'description' => 'Chance to slow the target and make their swings slower.', 'rarity' => 'RARE', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'berserk' => ['enabled' => true, 'display_name' => 'Berserk', 'description' => 'Chance of strength and mining fatigue for you.', 'rarity' => 'UNCOMMON', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.15],
		'featherweight' => ['enabled' => true, 'display_name' => 'Featherweight', 'description' => 'Chance to get a burst of haste.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'lifesteal' => ['enabled' => true, 'display_name' => 'Lifesteal', 'description' => 'Chance to steal health when attacking.', 'rarity' => 'LEGENDARY', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.05],
		'rebound' => ['enabled' => true, 'display_name' => 'Rebound', 'description' => 'Gain some health back after kills.', 'rarity' => 'LEGENDARY', 'max_level' => 3, 'has_chance' => false],
		'ravenous' => ['enabled' => true, 'display_name' => 'Ravenous', 'description' => 'Chance to regain hunger while fighting.', 'rarity' => 'UNCOMMON', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.15],
		'devour' => ['enabled' => true, 'display_name' => 'Devour', 'description' => 'Chance to restore food when you kill.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'doublestrike' => ['enabled' => true, 'display_name' => 'Double Strike', 'description' => 'A chance to strike twice.', 'rarity' => 'LEGENDARY', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'critical' => ['enabled' => true, 'display_name' => 'Critical', 'description' => 'Increase damage done with a critical hit.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => false],
		'barbarian' => ['enabled' => true, 'display_name' => 'Barbarian', 'description' => 'Chance to inflict more axe damage.', 'rarity' => 'LEGENDARY', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.05],
		'magnet' => ['enabled' => true, 'display_name' => 'Magnet', 'description' => 'Your attack pulls players towards you.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'forcefield' => ['enabled' => true, 'display_name' => 'Forcefield', 'description' => 'Chance to push away your opponent.', 'rarity' => 'COMMON', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.2],
		'disarm' => ['enabled' => true, 'display_name' => 'Disarm', 'description' => 'Chance to disarm your opponent.', 'rarity' => 'HEROIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'disarmor' => ['enabled' => true, 'display_name' => 'Disarmor', 'description' => 'Chance of unequipping a random armor piece from your opponent.', 'rarity' => 'LEGENDARY', 'max_level' => 8, 'has_chance' => true, 'chance' => 0.05],
		'fuddle' => ['enabled' => true, 'display_name' => 'Fuddle', 'description' => 'Disorganize your opponent\'s hotbar.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'decapitation' => ['enabled' => true, 'display_name' => 'Decapitation', 'description' => 'Chance to have your opponent\'s head drop on death.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'exalted' => ['enabled' => true, 'display_name' => 'Exalted', 'description' => 'A chance of removing your bad potion effects.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'epicness' => ['enabled' => true, 'display_name' => 'Epicness', 'description' => 'Display particles on attack.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => false],
		'hasten' => ['enabled' => true, 'display_name' => 'Hasten', 'description' => 'Chance to gain haste after breaking blocks.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.2],
		'telepathy' => ['enabled' => true, 'display_name' => 'Telepathy', 'description' => 'Broken blocks go straight into your inventory.', 'rarity' => 'UNCOMMON', 'max_level' => 4, 'has_chance' => false],
		'trench' => ['enabled' => true, 'display_name' => 'Trench', 'description' => 'Chance to break blocks in a wider area.', 'rarity' => 'EPIC', 'max_level' => 9, 'has_chance' => true, 'chance' => 0.1],
		'veinminer' => ['enabled' => true, 'display_name' => 'Vein Miner', 'description' => 'Mine an entire vein of ore.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => false],
		'timber' => ['enabled' => true, 'display_name' => 'Timber', 'description' => 'Chance to fell a whole tree in one hit.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'replenish' => ['enabled' => true, 'display_name' => 'Replenish', 'description' => 'Chance to restore food while mining.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'hellfire' => ['enabled' => true, 'display_name' => 'Hellfire', 'description' => 'Arrows set the target on fire.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
		'piercing' => ['enabled' => true, 'display_name' => 'Piercing', 'description' => 'Arrows inflict more damage.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => false],
		'archer' => ['enabled' => true, 'display_name' => 'Archer', 'description' => 'Increases damage dealt with bows.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => false],
		'snare' => ['enabled' => true, 'display_name' => 'Snare', 'description' => 'Chance to slow and fatigue enemies you shoot.', 'rarity' => 'RARE', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'perish' => ['enabled' => true, 'display_name' => 'Perish', 'description' => 'A chance to give the wither effect.', 'rarity' => 'UNCOMMON', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.15],
		'virus' => ['enabled' => true, 'display_name' => 'Virus', 'description' => 'A chance of giving the poison effect.', 'rarity' => 'UNCOMMON', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.15],
		'poisoned' => ['enabled' => true, 'display_name' => 'Poisoned', 'description' => 'Chance to poison whoever hits you.', 'rarity' => 'RARE', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'frozen' => ['enabled' => true, 'display_name' => 'Frozen', 'description' => 'Chance to slow whoever hits you.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'wither' => ['enabled' => true, 'display_name' => 'Wither', 'description' => 'Chance to wither whoever hits you.', 'rarity' => 'RARE', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
		'voodoo' => ['enabled' => true, 'display_name' => 'Voodoo', 'description' => 'Chance to weaken whoever hits you.', 'rarity' => 'RARE', 'max_level' => 6, 'has_chance' => true, 'chance' => 0.1],
		'curse' => ['enabled' => true, 'display_name' => 'Curse', 'description' => 'Chance to give your attacker mining fatigue.', 'rarity' => 'RARE', 'max_level' => 2, 'has_chance' => true, 'chance' => 0.1],
		'unholy' => ['enabled' => true, 'display_name' => 'Unholy', 'description' => 'Defend with weakness and wither.', 'rarity' => 'LEGENDARY', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.05],
		'divert' => ['enabled' => true, 'display_name' => 'Divert', 'description' => 'Chance to poison the attacker and regenerate.', 'rarity' => 'LEGENDARY', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.05],
		'angelic' => ['enabled' => true, 'display_name' => 'Angelic', 'description' => 'Heals you when damaged.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
		'molten' => ['enabled' => true, 'display_name' => 'Molten', 'description' => 'Chance of setting your attacker ablaze.', 'rarity' => 'UNCOMMON', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.15],
		'extinguish' => ['enabled' => true, 'display_name' => 'Extinguish', 'description' => 'Chance to put yourself out when on fire.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'springs' => ['enabled' => true, 'display_name' => 'Springs', 'description' => 'Gives jump boost.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => false],
		'obsidianshield' => ['enabled' => true, 'display_name' => 'Obsidianshield', 'description' => 'Gives permanent fire resistance.', 'rarity' => 'EPIC', 'max_level' => 1, 'has_chance' => false],
		'armored' => ['enabled' => true, 'display_name' => 'Armored', 'description' => 'Decreases damage from enemy swords by 2% per level.', 'rarity' => 'LEGENDARY', 'max_level' => 4, 'has_chance' => false],
		'heavy' => ['enabled' => true, 'display_name' => 'Heavy', 'description' => 'Decreases damage from enemy bows by 2% per level.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => false],
		'tank' => ['enabled' => true, 'display_name' => 'Tank', 'description' => 'Chance to decrease damage from enemy axes by 2% per level.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'dodge' => ['enabled' => true, 'display_name' => 'Dodge', 'description' => 'Chance to dodge physical attacks; higher while sneaking.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
		'arrowdeflect' => ['enabled' => true, 'display_name' => 'Arrow Deflect', 'description' => 'Chance to prevent an enemy arrow from dealing damage.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'creeperarmor' => ['enabled' => true, 'display_name' => 'Creeper Armor', 'description' => 'Chance to be immune to explosions; heals at higher levels.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'jellylegs' => ['enabled' => true, 'display_name' => 'Jelly Legs', 'description' => 'Chance to negate fall damage.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'chunky' => ['enabled' => true, 'display_name' => 'Chunky', 'description' => 'Chance to receive less damage.', 'rarity' => 'LEGENDARY', 'max_level' => 6, 'has_chance' => true, 'chance' => 0.05],
		'reflect' => ['enabled' => true, 'display_name' => 'Reflect', 'description' => 'Absorb enemy damage and reflect it back.', 'rarity' => 'UNCOMMON', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.15],
		'phoenix' => ['enabled' => true, 'display_name' => 'Phoenix', 'description' => 'Chance to revive yourself when killed.', 'rarity' => 'HEROIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'vampire' => ['enabled' => true, 'display_name' => 'Vampire', 'description' => 'Chance to heal a few seconds after you strike.', 'rarity' => 'RARE', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'bleed' => ['enabled' => true, 'display_name' => 'Bleed', 'description' => 'Make your opponent bleed.', 'rarity' => 'EPIC', 'max_level' => 6, 'has_chance' => true, 'chance' => 0.1],
		'permafrost' => ['enabled' => true, 'display_name' => 'Permafrost', 'description' => 'Chance to slow and bleed opponents.', 'rarity' => 'EPIC', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
		'ambit' => ['enabled' => true, 'display_name' => 'Ambit', 'description' => 'Damages entities around the target; radius grows with level.', 'rarity' => 'EPIC', 'max_level' => 7, 'has_chance' => true, 'chance' => 0.1],
		'cleave' => ['enabled' => true, 'display_name' => 'Cleave', 'description' => 'Damages players around the target; radius grows with level.', 'rarity' => 'EPIC', 'max_level' => 7, 'has_chance' => true, 'chance' => 0.1],
		'immolation' => ['enabled' => true, 'display_name' => 'Immolation', 'description' => 'Set all mobs around the target on fire.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'inflame' => ['enabled' => true, 'display_name' => 'Inflame', 'description' => 'Set all players around the target on fire.', 'rarity' => 'LEGENDARY', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'replanter' => ['enabled' => true, 'display_name' => 'Replanter', 'description' => 'Replants crops when harvested.', 'rarity' => 'UNCOMMON', 'max_level' => 1, 'has_chance' => false],
		'rebreather' => ['enabled' => true, 'display_name' => 'Rebreather', 'description' => 'Chance to regain air when mining underwater.', 'rarity' => 'COMMON', 'max_level' => 2, 'has_chance' => true, 'chance' => 0.2],
		'planter' => ['enabled' => true, 'display_name' => 'Planter', 'description' => 'Plant wheat seeds in an area by sneak + right-clicking farmland.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => false],
		'carrotplanter' => ['enabled' => true, 'display_name' => 'Carrot Planter', 'description' => 'Plant carrots in an area by sneak + right-clicking farmland.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => false],
		'potatoplanter' => ['enabled' => true, 'display_name' => 'Potato Planter', 'description' => 'Plant potatoes in an area by sneak + right-clicking farmland.', 'rarity' => 'COMMON', 'max_level' => 3, 'has_chance' => false],
		'launch' => ['enabled' => true, 'display_name' => 'Launch', 'description' => 'Launch yourself by right-clicking.', 'rarity' => 'LEGENDARY', 'max_level' => 3, 'has_chance' => false],
		'unbreakable' => ['enabled' => true, 'display_name' => 'Unbreakable', 'description' => 'Tools with this enchant never lose durability.', 'rarity' => 'HEROIC', 'max_level' => 1, 'has_chance' => false],
		'abiding' => ['enabled' => true, 'display_name' => 'Abiding', 'description' => 'Weapons with this enchant become unbreakable.', 'rarity' => 'LEGENDARY', 'max_level' => 1, 'has_chance' => false],
		'reforged' => ['enabled' => true, 'display_name' => 'Reforged', 'description' => 'Items lose durability more slowly.', 'rarity' => 'RARE', 'max_level' => 10, 'has_chance' => true, 'chance' => 0.1],
		'immortal' => ['enabled' => true, 'display_name' => 'Immortal', 'description' => 'Chance to stop armor losing durability.', 'rarity' => 'HEROIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.05],
		'soulbound' => ['enabled' => true, 'display_name' => 'Soulbound', 'description' => 'A chance to keep this item when you die.', 'rarity' => 'HEROIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'implants' => ['enabled' => true, 'display_name' => 'Implants', 'description' => 'Chance to restore food every few seconds.', 'rarity' => 'EPIC', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.1],
		'wings' => ['enabled' => true, 'display_name' => 'Wings', 'description' => 'Allows flying when worn.', 'rarity' => 'LEGENDARY', 'max_level' => 1, 'has_chance' => false],
		'trickster' => ['enabled' => true, 'display_name' => 'Trickster', 'description' => 'When hit, chance to teleport behind your opponent.', 'rarity' => 'RARE', 'max_level' => 8, 'has_chance' => true, 'chance' => 0.1],
		'ragdoll' => ['enabled' => true, 'display_name' => 'Ragdoll', 'description' => 'Chance to be pushed back when getting hit.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'convulse' => ['enabled' => true, 'display_name' => 'Convulse', 'description' => 'Chance to throw your attackers into the air.', 'rarity' => 'LEGENDARY', 'max_level' => 6, 'has_chance' => true, 'chance' => 0.05],
		'safeguard' => ['enabled' => true, 'display_name' => 'Safeguard', 'description' => 'Chance to gain damage resistance when defending.', 'rarity' => 'EPIC', 'max_level' => 2, 'has_chance' => true, 'chance' => 0.1],
		'lucid' => ['enabled' => true, 'display_name' => 'Lucid', 'description' => 'Chance to cure blindness and gain night vision when hit.', 'rarity' => 'LEGENDARY', 'max_level' => 3, 'has_chance' => true, 'chance' => 0.05],
		'disappear' => ['enabled' => true, 'display_name' => 'Disappear', 'description' => 'Chance to become invisible when low on health.', 'rarity' => 'EPIC', 'max_level' => 4, 'has_chance' => true, 'chance' => 0.1],
		'shockwave' => ['enabled' => true, 'display_name' => 'Shockwave', 'description' => 'Chance to push back your attacker when your health is low.', 'rarity' => 'RARE', 'max_level' => 5, 'has_chance' => true, 'chance' => 0.1],
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
				defaults: self::DEFAULTS[$key] ?? [],
			);
		}
		return $out;
	}
}