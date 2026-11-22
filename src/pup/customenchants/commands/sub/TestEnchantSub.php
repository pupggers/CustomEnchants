<?php

declare(strict_types=1);

namespace pup\customenchants\commands\sub;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\block\tile\Chest as ChestTile;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use pup\customenchants\api\ApplyStatus;
use pup\customenchants\Main;

final class TestEnchantSub extends BaseSubCommand
{
    private const array TYPES = [
        'sword', 'axe', 'bow', 'pickaxe', 'shovel', 'hoe',
        'helmet', 'chestplate', 'leggings', 'boots',
    ];

    /** @var list<array{name: string, type: string, enchants: list<string>, notes: list<string>}> */
    private const array INTERACTIONS = [
        ['name' => 'Dead Fire (sword)', 'type' => 'sword', 'enchants' => ['aronist', 'immolation'],
            'notes' => ['Aronist needs YOU to be burning.', 'Immolation only ignites bystanders,', 'so it never turns Aronist on.']],
        ['name' => 'Dead Fire (axe)', 'type' => 'axe', 'enchants' => ['aronist', 'inflame'],
            'notes' => ['Same problem: Inflame lights nearby players,', 'not you.']],
        ['name' => 'I-Frame Eaters', 'type' => 'sword', 'enchants' => ['doublestrike', 'critical', 'ambit', 'aronist'],
            'notes' => ['Each one hits again right after the swing.', 'The damage cooldown may swallow weaker hits.', 'Check that every bonus actually lands.']],
        ['name' => 'Slow Pile', 'type' => 'sword', 'enchants' => ['iceaspect', 'paralyze', 'interrupt'],
            'notes' => ['All stack Slowness / Fatigue on one target.', 'Only the strongest effect applies.']],
        ['name' => 'Exalted vs Berserk', 'type' => 'sword', 'enchants' => ['berserk', 'exalted'],
            'notes' => ['Exalted strips the Mining Fatigue', 'Berserk gives you. Intended upside?']],
        ['name' => 'Order Matters (pickaxe)', 'type' => 'pickaxe', 'enchants' => ['telepathy', 'autosmelt'],
            'notes' => ['Telepathy runs first and empties the drops,', 'so AutoSmelt has nothing to smelt.', 'Add AutoSmelt first to fix.']],
        ['name' => 'Guarded Extras (pickaxe)', 'type' => 'pickaxe', 'enchants' => ['autosmelt', 'telepathy', 'experience', 'trench'],
            'notes' => ['Trench breaks extra blocks, but nested break', 'events are guarded: extras skip AutoSmelt,', 'Telepathy and Experience.']],
        ['name' => 'Cancel Chain', 'type' => 'chestplate', 'enchants' => ['dodge', 'reflect', 'ragdoll', 'shockwave'],
            'notes' => ['When Dodge cancels a hit the defence loop', 'stops, so Reflect, Ragdoll and Shockwave', 'never run for that hit.']],
        ['name' => 'Overload x4 (helmet)', 'type' => 'helmet', 'enchants' => ['overload'],
            'notes' => ['Wear all four Overload pieces: the same toggled', 'enchant counts once (highest level), not x4.']],
        ['name' => 'Overload x4 (chestplate)', 'type' => 'chestplate', 'enchants' => ['overload'],
            'notes' => ['Wear all four Overload pieces: the same toggled', 'enchant counts once (highest level), not x4.']],
        ['name' => 'Overload x4 (leggings)', 'type' => 'leggings', 'enchants' => ['overload'],
            'notes' => ['Wear all four Overload pieces: the same toggled', 'enchant counts once (highest level), not x4.']],
        ['name' => 'Overload x4 (boots)', 'type' => 'boots', 'enchants' => ['overload'],
            'notes' => ['Wear all four Overload pieces: the same toggled', 'enchant counts once (highest level), not x4.']],
    ];

    /** @var list<array{name: string, type: string, enchants: list<string>, notes: list<string>}> */
    private const array SYNERGIES = [
        ['name' => 'Sustain Duelist', 'type' => 'sword', 'enchants' => ['lifesteal', 'vampire', 'rebound', 'ravenous', 'devour'],
            'notes' => ['Heals and feeds you on hit and on kill.']],
        ['name' => 'Controller', 'type' => 'sword', 'enchants' => ['iceaspect', 'blind', 'confuse', 'famine', 'poison', 'magnet'],
            'notes' => ['Six different debuffs, no overlap:', 'slow, blind, nausea, hunger, poison, pull.']],
        ['name' => 'Pyro Sword', 'type' => 'sword', 'enchants' => ['aronist'],
            'notes' => ['Pair with the Pyro Bow: a Hellfire arrow', 'lights the target, then Aronist hits harder.']],
        ['name' => 'Pyro Bow', 'type' => 'bow', 'enchants' => ['hellfire', 'archer', 'piercing', 'snare'],
            'notes' => ['Hellfire ignites, Archer + Piercing add damage,', 'Snare keeps the target in range.']],
        ['name' => 'Executioner', 'type' => 'axe', 'enchants' => ['barbarian', 'bleed', 'cleave', 'reaper', 'inflame'],
            'notes' => ['Burst + bleed + wither on the target,', 'Cleave and Inflame hit nearby players.']],
        ['name' => 'Smart Miner', 'type' => 'pickaxe', 'enchants' => ['autosmelt', 'telepathy', 'experience', 'hasten', 'rebreather', 'unbreakable'],
            'notes' => ['AutoSmelt is added before Telepathy so', 'Telepathy collects the smelted drops.']],
        ['name' => 'Vein Hauler', 'type' => 'pickaxe', 'enchants' => ['veinminer', 'hasten', 'replenish', 'unbreakable'],
            'notes' => ['Haste keeps up with big veins,', 'Replenish keeps you fed.']],
        ['name' => 'Lumberjack', 'type' => 'axe', 'enchants' => ['timber', 'hasten', 'unbreakable'],
            'notes' => ['Timber fells the tree, Haste keeps pace.']],
        ['name' => 'Digger', 'type' => 'shovel', 'enchants' => ['trench', 'hasten', 'unbreakable'],
            'notes' => ['Wide dig with Haste and no wear.']],
        ['name' => 'Farmer', 'type' => 'hoe', 'enchants' => ['planter', 'replanter', 'speed'],
            'notes' => ['Planter plants, Replanter replants on harvest,', 'Speed keeps you moving.']],
        ['name' => 'Tank (helmet)', 'type' => 'helmet', 'enchants' => ['overload', 'aquatic', 'glowing', 'implants'],
            'notes' => ['Different toggled enchants on each piece', 'all stack. Wear the full Tank set.']],
        ['name' => 'Tank (chestplate)', 'type' => 'chestplate', 'enchants' => ['chunky', 'armored', 'heavy', 'phoenix'],
            'notes' => ['Damage reducers first, Phoenix last so it', 'checks the already-reduced damage.']],
        ['name' => 'Tank (leggings)', 'type' => 'leggings', 'enchants' => ['obsidianshield', 'safeguard', 'tank'],
            'notes' => ['Fire immunity, a resistance proc and', 'axe damage reduction.']],
        ['name' => 'Tank (boots)', 'type' => 'boots', 'enchants' => ['gears', 'springs', 'jellylegs', 'convulse'],
            'notes' => ['Speed, jump, no fall damage, and a', 'launch on whoever hits you.']],
        ['name' => 'Thorns Chestplate', 'type' => 'chestplate', 'enchants' => ['molten', 'frozen', 'poisoned', 'wither', 'voodoo'],
            'notes' => ['Whoever hits you gets burned, slowed,', 'poisoned, withered and weakened.']],
    ];

    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "test", "Spawn enchant test items (1=coverage, 2=conflicts, 3=synergies, 4=interactions)");
        $this->setPermission("enchant.command.test");
    }

    /** @throws ArgumentOrderException */
    protected function prepare(): void
    {
        $this->registerArgument(0, new IntegerArgument('mode'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;

        $mode = (int) ($args['mode'] ?? 0);
        $skipped = [];
        $unplaceable = [];
        $apiFailures = [];

        switch ($mode) {
            case 1:
                $items = $this->buildCoverage($skipped, $unplaceable);
                break;
            case 2:
                $items = $this->buildConflicts($skipped, $apiFailures);
                break;
            case 3:
                $items = $this->buildSets(self::SYNERGIES, TF::GREEN . 'SYNERGY', $skipped, false);
                break;
            case 4:
                $items = $this->buildSets(self::INTERACTIONS, TF::GOLD . 'INTERACTION', $skipped, true);
                break;
            default:
                $sender->sendMessage(TF::RED . "Usage: /ce test <1|2|3|4>  (1 = coverage, 2 = conflicts, 3 = synergies, 4 = interactions)");
                return;
        }

        if ($items === []) {
            $sender->sendMessage(TF::RED . "Nothing to spawn: no enabled enchantments matched.");
            return;
        }

        [$chests, $overflow] = $this->placeInChests($sender, $items);

        $sender->sendMessage(TF::GREEN . "Mode $mode: " . count($items) . " item(s) in $chests chest(s) in front of you.");
        if ($mode === 2) {
            $sender->sendMessage($apiFailures === []
                ? TF::GREEN . "API check: every conflict pair is blocked for books and /ce add."
                : TF::RED . "API check FAILED for " . count($apiFailures) . " pair(s): " . implode(', ', $apiFailures));
        }
        if ($overflow > 0) {
            $sender->sendMessage(TF::YELLOW . "$overflow item(s) did not fit and went to your inventory / the ground (not enough free space for chests).");
        }
        if ($unplaceable !== []) {
            $sender->sendMessage(TF::YELLOW . "No test item type accepts: " . implode(', ', $unplaceable));
        }
        foreach (array_slice($skipped, 0, 10) as $line) {
            $sender->sendMessage(TF::YELLOW . "Skipped " . $line);
        }
        if (count($skipped) > 10) {
            $sender->sendMessage(TF::YELLOW . "...and " . (count($skipped) - 10) . " more skipped.");
        }
    }

    /**
     * @param list<string> $skipped
     * @param list<string> $unplaceable
     * @return list<Item>
     */
    private function buildCoverage(array &$skipped, array &$unplaceable): array
    {
        $manager = $this->main->getEnchantManager();
        $enchants = $manager->getEnabled();
        ksort($enchants);

        $items = [];

        foreach ($enchants as $key => $enchant) {
            $placed = false;

            foreach (self::TYPES as $type) {
                $item = $this->makeItem($type);
                if (!$manager->canApply($key, $item)) {
                    continue;
                }
                $placed = true;
                $items[] = $this->finish(
                    $item,
                    TF::RESET . TF::YELLOW . $manager->displayName($key) . TF::GRAY . " [$type]",
                    $key,
                    [$key],
                    $skipped,
                    ["Coverage test: max level, $type"],
                    true
                );
            }

            if (!$placed) {
                $unplaceable[] = $key;
            }
        }

        return $items;
    }

    /**
     * @param list<string> $skipped
     * @param list<string> $apiFailures
     * @return list<Item>
     */
    private function buildConflicts(array &$skipped, array &$apiFailures): array
    {
        $manager = $this->main->getEnchantManager();
        $items = [];

        foreach ($manager->getConflictPairs() as [$a, $b]) {
            if ($manager->getByKey($a) === null || $manager->getByKey($b) === null) {
                $skipped[] = "$a x $b (one of them is disabled)";
                continue;
            }

            $type = $this->firstSharedType($a, $b);
            if ($type === null) {
                continue;
            }

            foreach ([[$a, $b], [$b, $a]] as [$first, $second]) {
                $probe = $this->makeItem($type);
                $manager->apply($probe, $first, 1);
                if ($manager->check($probe, $second, 1)->status !== ApplyStatus::CONFLICT) {
                    $apiFailures[] = "$first+$second";
                }
            }

            $owner = $manager->getOwner($a) ?? $manager->getOwner($b);
            $notes = [
                'BLOCKED: ' . $manager->displayName($a) . ' x ' . $manager->displayName($b),
                'Books and /ce add refuse this combo.',
                'Spawned with force so you can inspect it.',
            ];
            if ($owner !== null) {
                $notes[] = 'From addon: ' . $owner->getName();
            }

            $items[] = $this->finish(
                $this->makeItem($type),
                TF::RESET . TF::RED . 'CONFLICT' . TF::GRAY . ' - ' . TF::WHITE . $manager->displayName($a) . ' x ' . $manager->displayName($b),
                "$a x $b",
                [$a, $b],
                $skipped,
                $notes,
                true
            );
        }

        return $items;
    }

    private function firstSharedType(string $a, string $b): ?string
    {
        $manager = $this->main->getEnchantManager();
        foreach (self::TYPES as $type) {
            $item = $this->makeItem($type);
            if ($manager->canApply($a, $item) && $manager->canApply($b, $item)) {
                return $type;
            }
        }
        return null;
    }

    /**
     * @param list<array{name: string, type: string, enchants: list<string>, notes: list<string>}> $sets
     * @param list<string> $skipped
     * @return list<Item>
     */
    private function buildSets(array $sets, string $tag, array &$skipped, bool $force): array
    {
        $items = [];

        foreach ($sets as $set) {
            $items[] = $this->finish(
                $this->makeItem($set['type']),
                TF::RESET . $tag . TF::GRAY . " - " . TF::WHITE . $set['name'],
                $set['name'],
                $set['enchants'],
                $skipped,
                $set['notes'],
                $force
            );
        }

        return $items;
    }

    /**
     * @param list<string> $keys
     * @param list<string> $skipped
     * @param list<string> $notes
     */
    private function finish(Item $item, string $customName, string $label, array $keys, array &$skipped, array $notes, bool $force): Item
    {
        $manager = $this->main->getEnchantManager();

        $item->setCustomName($customName);
        $item->setLore(array_map(static fn(string $line): string => TF::RESET . TF::DARK_GRAY . $line, $notes));

        foreach ($keys as $key) {
            $enchant = $manager->getByKey($key);
            if ($enchant === null) {
                $skipped[] = "$key on $label (not registered or disabled)";
                continue;
            }

            $result = $manager->apply($item, $key, $enchant->getMaxLevel(), $force);
            if (!$result->isOk() && !($force && $result->status->isBypassable())) {
                $other = $result->other !== null ? " with {$result->other}" : '';
                $skipped[] = "$key on $label ({$result->status->name}$other)";
            }
        }

        return $manager->rebuildLore($item);
    }

    private function makeItem(string $type): Item
    {
        return match ($type) {
            'sword' => VanillaItems::DIAMOND_SWORD(),
            'axe' => VanillaItems::DIAMOND_AXE(),
            'bow' => VanillaItems::BOW(),
            'pickaxe' => VanillaItems::DIAMOND_PICKAXE(),
            'shovel' => VanillaItems::DIAMOND_SHOVEL(),
            'hoe' => VanillaItems::DIAMOND_HOE(),
            'helmet' => VanillaItems::DIAMOND_HELMET(),
            'chestplate' => VanillaItems::DIAMOND_CHESTPLATE(),
            'leggings' => VanillaItems::DIAMOND_LEGGINGS(),
            'boots' => VanillaItems::DIAMOND_BOOTS(),
        };
    }

    /**
     * @param list<Item> $items
     * @return array{0: int, 1: int}
     */
    private function placeInChests(Player $player, array $items): array
    {
        $world = $player->getWorld();
        $facing = $player->getHorizontalFacing();
        $right = Facing::rotateY($facing, true);
        $start = $player->getPosition()->floor()->getSide($facing, 3);

        $step = 0;
        $chests = 0;
        $leftover = [];

        foreach (array_chunk($items, 27) as $chunk) {
            $pos = null;
            while ($pos === null && $step < 80) {
                $candidate = $start->getSide($right, $step * 2);
                $step++;
                if ($world->getBlock($candidate)->canBeReplaced()) {
                    $pos = $candidate;
                }
            }

            if ($pos === null) {
                array_push($leftover, ...$chunk);
                continue;
            }

            $world->setBlock($pos, VanillaBlocks::CHEST());
            $tile = $world->getTile($pos);
            if (!$tile instanceof ChestTile) {
                array_push($leftover, ...$chunk);
                continue;
            }

            $chests++;
            foreach ($chunk as $item) {
                foreach ($tile->getRealInventory()->addItem($item) as $rest) {
                    $leftover[] = $rest;
                }
            }
        }

        foreach ($leftover as $item) {
            foreach ($player->getInventory()->addItem($item) as $rest) {
                $world->dropItem($player->getPosition(), $rest);
            }
        }

        return [$chests, count($leftover)];
    }
}
