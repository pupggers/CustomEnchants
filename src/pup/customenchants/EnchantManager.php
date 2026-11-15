<?php

namespace pup\customenchants;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\lang\Translatable;
use pup\customenchants\enchants\armor\OverloadEnchant;
use pup\customenchants\enchants\armor\ShuffleEnchant;
use pup\customenchants\enchants\armor\boots\BunnyEnchant;
use pup\customenchants\enchants\armor\boots\GearsEnchant;
use pup\customenchants\enchants\armor\boots\TakeOffEnchant;
use pup\customenchants\enchants\armor\helmet\AquaticEnchant;
use pup\customenchants\enchants\armor\helmet\GlowingEnchant;
use pup\customenchants\enchants\bow\TeleportEnchant;
use pup\customenchants\enchants\sword\AronistEnchant;
use pup\customenchants\enchants\sword\BlindEnchant;
use pup\customenchants\enchants\sword\DazeEnchant;
use pup\customenchants\enchants\sword\ZuesEnchant;
use pup\customenchants\enchants\tools\ExperienceEnchant;
use pup\customenchants\enchants\tools\RestoreEnchant;
use pup\customenchants\enchants\tools\hoe\SpeedEnchant;
use pup\customenchants\enchants\tools\pickaxe\AutoSmeltEnchant;
use pup\customenchants\enchants\tools\pickaxe\DrillEnchant;
use pup\customenchants\enchants\tools\pickaxe\FeedEnchant;
use pup\customenchants\enchants\tools\pickaxe\HasteEnchant;
use pup\customenchants\items\ItemRegistry;
use pup\customenchants\types\WeaponEnchant;
use pup\customenchants\utils\Rarity;
use RuntimeException;

final class EnchantManager
{
    private const array DEFINITIONS = [
        'feed' => ['id' => 1000, 'class' => FeedEnchant::class, 'flags' => [ItemFlags::PICKAXE]],
        'haste' => ['id' => 1001, 'class' => HasteEnchant::class, 'flags' => [ItemFlags::PICKAXE]],
        'drill' => ['id' => 1002, 'class' => DrillEnchant::class, 'flags' => [ItemFlags::PICKAXE]],
        'speed' => ['id' => 1003, 'class' => SpeedEnchant::class, 'flags' => [ItemFlags::HOE, ItemFlags::PICKAXE]],
        'zues' => ['id' => 1004, 'class' => ZuesEnchant::class, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE]],
        'daze' => ['id' => 1005, 'class' => DazeEnchant::class, 'flags' => [ItemFlags::SWORD]],
        'blind' => ['id' => 1006, 'class' => BlindEnchant::class, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE]],
        'aronist' => ['id' => 1007, 'class' => AronistEnchant::class, 'flags' => [ItemFlags::SWORD, ItemFlags::AXE]],
        'teleport' => ['id' => 1008, 'class' => TeleportEnchant::class, 'flags' => [ItemFlags::BOW]],
        'overload' => ['id' => 1009, 'class' => OverloadEnchant::class, 'flags' => [ItemFlags::ARMOR]],
        'glowing' => ['id' => 1010, 'class' => GlowingEnchant::class, 'flags' => [ItemFlags::HEAD]],
        'gears' => ['id' => 1011, 'class' => GearsEnchant::class, 'flags' => [ItemFlags::FEET]],
        'bunny' => ['id' => 1012, 'class' => BunnyEnchant::class, 'flags' => [ItemFlags::FEET]],
        'restore' => ['id' => 1013, 'class' => RestoreEnchant::class, 'flags' => [ItemFlags::DIG]],
        'shuffle' => ['id' => 1014, 'class' => ShuffleEnchant::class, 'flags' => [ItemFlags::ARMOR]],
        'takeoff' => ['id' => 1015, 'class' => TakeOffEnchant::class, 'flags' => [ItemFlags::FEET]],
        'aquatic' => ['id' => 1016, 'class' => AquaticEnchant::class, 'flags' => [ItemFlags::HEAD]],
        'autosmelt' => ['id' => 1017, 'class' => AutoSmeltEnchant::class, 'flags' => [ItemFlags::PICKAXE]],
        'experience' => ['id' => 1018, 'class' => ExperienceEnchant::class, 'flags' => [ItemFlags::DIG]],
    ];

    private array $data;
    /** @var array<string, Enchantment> */
    private array $registered = [];

    public function __construct(private readonly Main $plugin)
    {
        $this->data = $this->loadData();
        $this->registerEnchants();
        ItemRegistry::init();
        EnchantPlaceholders::register($this);
    }

    /** @return array<string, array<string, mixed>> */
    private function loadData(): array
    {
        $path = $this->plugin->getDataFolder() . 'enchantments.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Could not read enchantments.json: it must contain a JSON object.');
        }
        foreach (self::DEFINITIONS as $key => $_definition) {
            if (!isset($decoded[$key])) {
                $decoded[$key] = $this->defaultData($key);
            }
        }
        return $decoded;
    }

    /** @return array<string, mixed> */
    private function defaultData(string $key): array
    {
        return ['enabled' => true, 'display_name' => ucwords(str_replace('_', ' ', $key)), 'description' => 'Configure this enchantment before enabling it.', 'rarity' => 'COMMON', 'max_level' => 1, 'has_chance' => false, 'chance' => 0.1];
    }

    private function registerEnchants(): void
    {
        foreach (self::DEFINITIONS as $key => $definition) {
            $data = $this->data[$key];
            if (($data['enabled'] ?? true) !== true) {
                continue;
            }
            try {
                $class = $definition['class'];
                $enchant = new $class((string) ($data['display_name'] ?? $key), Rarity::fromName((string) ($data['rarity'] ?? 'COMMON')), (string) ($data['description'] ?? ''), max(1, (int) ($data['max_level'] ?? 1)), ...$definition['flags']);
                if (($data['has_chance'] ?? false) === true) {
                    $enchant->setBaseChance((float) ($data['chance'] ?? $data['base_chance'] ?? 0.1));
                }
                EnchantmentIdMap::getInstance()->register($definition['id'], $enchant);
                $this->registered[$key] = $enchant;
                $this->plugin->getLogger()->info("Registered {$enchant->getName()} ($key)");
            } catch (RuntimeException $exception) {
                $this->plugin->getLogger()->error("Could not register $key: {$exception->getMessage()}");
            }
        }
    }

    public static function normalizeKey(string $key): string
    {
        return strtolower(str_replace([' ', '-'], '', $key));
    }

    public function getByKey(string $key): ?Enchantment
    {
        return $this->registered[self::normalizeKey($key)] ?? null;
    }

    public function getIdByKey(string $key): ?int
    {
        return self::DEFINITIONS[self::normalizeKey($key)]['id'] ?? null;
    }

    /** @return array<string, Enchantment> */
    public function getEnabled(): array
    {
        return $this->registered;
    }

    public function canApply(string $key, Item $item): bool
    {
        $definition = self::DEFINITIONS[self::normalizeKey($key)] ?? null;
        if ($definition === null || $this->getByKey($key) === null) {
            return false;
        }
        $itemFlags = self::getItemFlags($item);
        foreach ($definition['flags'] as $flag) {
            if (($itemFlags & $flag) !== 0) {
                return true;
            }
        }
        return false;
    }

    private static function getItemFlags(Item $item): int
    {
        return match (true) {
            $item instanceof Sword => ItemFlags::SWORD,
            $item instanceof Bow => ItemFlags::BOW,
            $item instanceof Pickaxe => ItemFlags::PICKAXE,
            $item instanceof Axe => ItemFlags::AXE,
            $item instanceof Shovel => ItemFlags::SHOVEL,
            $item instanceof Hoe => ItemFlags::HOE,
            $item instanceof Tool => ItemFlags::DIG,
            $item instanceof Armor => match ($item->getArmorSlot()) {
                ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
                ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
                ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
                ArmorInventory::SLOT_FEET => ItemFlags::FEET,
                default => ItemFlags::ARMOR,
            },
            default => ItemFlags::NONE,
        };
    }

    public function rebuildLore(Item $item): Item
    {
        if ($item->getNamedTag()->getTag('hideEnchantments') !== null) {
            return $item;
        }
        $oldGenerated = json_decode($item->getNamedTag()->getString('custom_enchants_lore', '[]'), true);
        $oldGenerated = is_array($oldGenerated) ? $oldGenerated : [];
        $lore = array_values(array_filter($item->getLore(), fn(string $line): bool => !in_array($line, $oldGenerated, true)));
        $generated = [];
        foreach ($item->getEnchantments() as $instance) {
            $enchant = $instance->getType();
            if (!$enchant instanceof CustomEnchant && !$enchant instanceof WeaponEnchant) {
                continue;
            }
            $name = $enchant->getName();
            $name = $name instanceof Translatable ? $name->getText() : $name;
            $level = $instance->getLevel();
            $generated[] = $this->plugin->getMessages()->format((string) $this->plugin->getConfig()->getNested('enchant_lore.entry_format', '&r&8» &r{color}{name} {level}'), null, ['color' => Rarity::getColor($enchant->getRarity()), 'name' => $name, 'level' => $this->plugin->getConfig()->getNested('enchant_lore.roman_numerals', true) ? self::intToRoman($level) : $level]);
        }
        if ($generated !== []) {
            sort($generated, SORT_NATURAL | SORT_FLAG_CASE);
            array_unshift($generated, $this->plugin->getMessages()->format((string) $this->plugin->getConfig()->getNested('enchant_lore.header', '&r&dEnchantments:')));
            $lore = [...$lore, ...$generated];
        }
        $item->getNamedTag()->setString('custom_enchants_lore', json_encode($generated, JSON_UNESCAPED_UNICODE));
        $item->setLore($lore);
        return $item;
    }

    public static function intToRoman(int $number): string
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $result = '';
        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }
        return $result;
    }
}
