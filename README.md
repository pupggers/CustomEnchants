# CustomEnchants

CustomEnchants is a feature-rich PocketMine-MP plugin that introduces a large collection of custom enchantments, an in-game Enchanter, configurable enchantment books, and a dedicated API for third-party addon developers.

Designed for flexibility, CustomEnchants allows server owners to customize enchantments, balance their rarities, configure enchantment combinations, and extend the system through independently distributed addons.

Rather than keeping everything inside one plugin, CustomEnchants provides a centralized enchantment registry that allows other plugins to register and manage their own enchantments.

---

## Features

### Enchantment System

- A large collection of built-in custom enchantments.
- Support for armor, weapons, bows, and tools.
- Configurable enchantment names, descriptions, rarities, and maximum levels.
- Configurable activation chances and maximum-level activation chances.
- Custom enchantment lore with rarity-based formatting.
- Roman numeral enchantment levels.
- Persistent enchantment ID assignments.
- Support for custom enchantment rarities.

### Enchantment Compatibility

CustomEnchants provides a configurable relationship system for controlling which enchantments can coexist.

| Property | Description |
|---|---|
| `conflicts` | Prevents two enchantments from being applied to the same item. |
| `requires` | Requires another enchantment at a specified level. |
| `replaces` | Removes specified enchantments when the new enchantment is applied. |

These relationships can be configured individually for each enchantment.

Built-in conflict groups are also provided, with per-enchantment configuration taking precedence.

### Enchantment Books

Customize how enchantment books are generated and applied.

- Configurable book success rates.
- Configurable destruction rates.
- Randomized enchantment levels.
- Book combining.
- Configurable rarity-based book generation.
- Magic Dust for improving book success rates.
- White Scrolls and Holy Scrolls for item protection.
- Black Scrolls for removing enchantments.

### Enchanter

An in-game shop accessible through `/ce`.

Server owners can configure:

- Shop appearance and title.
- Enchantment book prices.
- Available rarity tiers.
- Scroll prices.
- Whether individual items are available for purchase.

Prices can use either experience levels or experience points.

### Tinkerer

Players can exchange enchantment books for Magic Dust.

Configure the amount of Magic Dust awarded for each rarity, including custom rarities registered by addons.

### Addon System

Extend CustomEnchants without modifying its source code.

- Install addons from GitHub repositories.
- Download addon PHARs from GitHub releases.
- Automatically load installed addons.
- Remove installed addons.
- View installed addon information.
- Automatically resolve and install supported hard dependencies.
- Support separate addon repositories and aliases.
- Maintain an addon installation manifest.

Addons are distributed independently from the main plugin.

### Developer API

CustomEnchants includes an API for developers who want to build their own enchantment addons.

The API supports:

- Registering individual enchantment definitions.
- Registering multiple enchantments.
- Accessing the centralized enchantment manager.
- Checking enchantment application requirements.
- Applying and removing enchantments.
- Registering custom rarity tiers.
- Retrieving enchantments on items.
- Reading enchantment levels across equipped armor.
- Registering custom messages.
- Registering custom PlaceholderAPI placeholders.

---

## Requirements

CustomEnchants targets **PocketMine-MP API 5**.

For source installations, use Composer to install the required dependencies and prepare the plugin using the appropriate PocketMine development tools.

---

## Installation

### Server Installation

1. Download the latest CustomEnchants PHAR from the [GitHub Releases](https://github.com/pupggers/CustomEnchants/releases) page.
2. Place the PHAR inside your server's `plugins/` directory.
3. Ensure the required dependencies are available.
4. Start your server.

CustomEnchants will generate its configuration and resource files automatically.

### Generated Files

The plugin creates the following files inside its data directory:

| File | Description |
|---|---|
| `enchantments.json` | Built-in enchantment settings. |
| `items.json` | Custom item settings. |
| `enchant_ids.json` | Persistent enchantment ID assignments. |
| `addons.json` | Installed addon manifest. |
| `addons/` | Directory containing installed addon PHARs. |

---

## Commands

The main command is `/ce`, with `/enchanter` as an alternative.

| Command | Description | Permission |
|---|---|---|
| `/ce` | Opens the Enchanter menu. | `enchant.command` |
| `/ce add <enchant> [level] [force]` | Applies an enchantment to your held item. | `enchant.command.add` |
| `/ce remove <enchant>` | Removes an enchantment from your held item. | `enchant.command.remove` |
| `/ce addon list` | Lists installed addons. | `enchant.command.addon` |
| `/ce addon install <name>` | Downloads and installs an addon. | `enchant.command.addon` |
| `/ce addon remove <name>` | Removes an installed addon. | `enchant.command.addon` |

The addon management commands can also be used from the server console.

### Force Application

The optional `force` argument on `/ce add` allows administrators to bypass enchantment conflict and requirement checks.

Example:

```text
/ce add heavy 5
/ce add heavy 5 force
```

Use force application carefully, as it can create enchantment combinations that would normally be rejected.

---

## Permissions

| Permission | Default | Description |
|---|---|---|
| `enchant.command` | Everyone | Access the Enchanter command. |
| `enchant.command.tinkerer` | Everyone | Access the Tinkerer. |
| `enchant.command.add` | Operators | Apply enchantments using commands. |
| `enchant.command.remove` | Operators | Remove enchantments using commands. |
| `enchant.command.addon` | Operators | Manage enchantment addons. |

Permissions can be customized through your server's permission management system.

---

## Configuration

CustomEnchants is designed to be configurable without modifying its source code.

### Rarities

Rarity tiers control enchantment appearance, formatting, and enchantment book generation weights.

Default rarities include:

| Rarity | Default Weight |
|---|---:|
| Common | 50 |
| Uncommon | 25 |
| Rare | 15 |
| Epic | 10 |
| Legendary | 5 |
| Mastery | 3 |
| Heroic | 0 |

Example:

```yaml
rarities:
  COMMON:
    weight: 50
    display_color: "{gray}"
    enabled: true

  LEGENDARY:
    weight: 5
    display_color: "{gold}"
    enabled: true

  HEROIC:
    weight: 0
    display_color: "{dark_red}"
    enabled: true
```

Weights determine the relative selection probability when generating books. A weight of `0` prevents a rarity from being selected through weighted generation.

### Enchantment Settings

Enchantment settings are stored in `enchantments.json`.

Example:

```json
{
    "heavy": {
        "enabled": true,
        "display_name": "Heavy",
        "description": "Decreases damage from enemy bows.",
        "rarity": "EPIC",
        "max_level": 5,
        "chance": 0.1,
        "max_chance": 0.7
    }
}
```

Available settings include:

| Setting | Description |
|---|---|
| `enabled` | Enables or disables the enchantment. |
| `display_name` | Custom enchantment display name. |
| `description` | Enchantment description. |
| `rarity` | Enchantment rarity. |
| `max_level` | Maximum enchantment level. |
| `has_chance` | Whether the enchantment uses an activation chance. |
| `chance` | Base activation chance. |
| `max_chance` | Activation chance at maximum level. |
| `conflicts` | Enchantments that cannot coexist with this enchantment. |
| `requires` | Required enchantments and minimum levels. |
| `replaces` | Enchantments removed when this enchantment is applied. |

Activation chances are represented as decimal values. For example, `0.15` represents 15%.

### Enchantment Requirements

Example:

```json
{
    "heavy": {
        "requires": {
            "armored": 3
        }
    }
}
```

In this example, Heavy requires Armored III to already be present on the item.

### Enchantment Conflicts

Example:

```json
{
    "heavy": {
        "conflicts": [
            "lightweight",
            "featherweight"
        ]
    }
}
```

The configured enchantments cannot coexist with Heavy.

### Heroic Upgrades and Replacements

The `replaces` property defines which enchantments should be removed when another enchantment is applied.

Example:

```json
{
    "unbreakable": {
        "replaces": [
            "reforged"
        ]
    }
}
```

This is useful for creating upgrade paths between enchantments.

### Enchantment Books

Configure book success rates, destruction rates, and generated levels.

```yaml
book_chance: true

enchant_books:
  success_min: 30
  success_max: 100

  levels:
    COMMON: random
    UNCOMMON: random
    RARE: random
    EPIC: random
    LEGENDARY: random
    default: 1
```

When book chances are enabled, failed applications can result in item destruction according to the configured rates.

Protection scrolls can prevent item destruction.

### Enchanter Shop

Configure the cost of books and scrolls.

```yaml
shop:
  title: "{dark_purple}Enchanter"

  books:
    COMMON: "level:5"
    UNCOMMON: "level:10"
    RARE: "level:15"
    EPIC: "level:25"
    LEGENDARY: "level:35"
    HEROIC: false
    default: "level:50"

  items:
    white_scroll: "level:30"
    holy_scroll: "level:60"
    black_scroll: "level:20"
```

Prices support:

- `level:<amount>` – Experience levels.
- `exp:<amount>` – Experience points.
- `false` – Disables the item from the shop.

### Tinkerer

Configure how much Magic Dust players receive when exchanging enchantment books.

```yaml
tinkerer:
  title: "{gold}Tinkerer"
  default_dust: 1

  dust:
    COMMON: 1
    UNCOMMON: 1
    RARE: 2
    EPIC: 2
    LEGENDARY: 3
    MASTERY: 4
    HEROIC: 5
```

### Addon Repositories

Addon repositories can be configured in `config.yml`.

```yaml
addons:
  repositories:
    Cosmic: "owner/CustomEnchantsCosmic"
    Fabled: "owner/CustomEnchantsFabled"

  allow_any_repository: false
```

The repository names act as aliases for addon installation.

For example:

```text
/ce addon install Cosmic
```

Alternatively, when `allow_any_repository` is enabled, an addon can be requested using its `owner/repository` identifier.

**Security notice:** Addons are executable PHP code. Only install addons from developers and repositories you trust. Allowing arbitrary repositories expands the sources from which server code can be downloaded.

---

## Developer Documentation

CustomEnchants exposes its API through:

```php
pup\customenchants\api\CustomEnchantsAPI
```

Addon developers should use this API instead of implementing their own enchantment registry.

### Registering Enchantments

Enchantments are registered using `EnchantDefinition`.

Each definition provides:

- A unique enchantment key.
- An enchantment ID.
- Applicable item enchantment tags.
- An enchantment factory.
- Optional conflicts.
- Optional requirements.
- Optional replacements.
- Default configuration values.

Multiple enchantments can be registered using:

```php
CustomEnchantsAPI::registerAll($this, $definitions);
```

The method loads the addon's `enchantments.json`, applies its configuration, and registers the supplied definitions with the central manager.

### Enchantment Registration Example

```php
use pocketmine\item\enchantment\ItemEnchantmentTags;
use pup\customenchants\api\CustomEnchantsAPI;
use pup\customenchants\api\EnchantDefinition;

// Register your enchantment definitions during your addon's enable process.

$definitions = [
    new EnchantDefinition(
        'example_enchant',
        1200,
        [ItemEnchantmentTags::SWORD],
        static fn(string $name, int $rarity, string $description, int $maxLevel) =>
            new ExampleEnchant($name, $rarity, $description, $maxLevel),
        conflicts: [],
        requires: [],
        replaces: [],
        defaults: [
            'enabled' => true,
            'display_name' => 'Example Enchant',
            'description' => 'An example custom enchantment.',
            'rarity' => 'RARE',
            'max_level' => 3
        ]
    )
];

$registered = CustomEnchantsAPI::registerAll($this, $definitions);
```

`ExampleEnchant` must extend PocketMine's `Enchantment` and implement CustomEnchants' `CustomEnchantInterface`.

The numeric ID in this example is illustrative. Select an appropriate unique ID for your addon.

### Checking and Applying Enchantments

Use the API to validate and apply enchantments.

```php
use pup\customenchants\api\CustomEnchantsAPI;

$result = CustomEnchantsAPI::check($item, 'example_enchant', 1);

if($result->isOk()){
    CustomEnchantsAPI::apply($item, 'example_enchant', 1);
}
```

`ApplyResult` provides the result of an enchantment operation, including its status and relevant failure information.

### Retrieving Enchantments

```php
$enchants = CustomEnchantsAPI::getEnchants($item);
```

This returns an associative array containing enchantment keys and their respective levels.

### Reading Equipped Armor Levels

```php
$totalLevel = CustomEnchantsAPI::getTotalWornLevel(
    $player,
    'example_enchant'
);
```

To retrieve the level contributed by each armor slot:

```php
$levels = CustomEnchantsAPI::getWornLevels(
    $player,
    'example_enchant'
);
```

### Registering Custom Rarities

Addons can register additional rarity tiers.

```php
CustomEnchantsAPI::registerRarity(
    $this,
    'CUSTOM',
    100,
    'Custom',
    '{light_purple}',
    10
);
```

Register custom rarities before registering enchantments that use them.

Keep rarity numeric values stable between releases because they are stored on items.

### Custom Messages

Addons can register their own message keys.

```php
CustomEnchantsAPI::registerMessages($this, [
    'example_success' => '{green}Your enchantment activated!'
]);
```

Messages can then be sent using:

```php
CustomEnchantsAPI::sendMessage(
    $player,
    'example_success'
);
```

### Custom Placeholders

Addons can register additional PlaceholderAPI placeholders.

```php
CustomEnchantsAPI::registerPlaceholder(
    'example',
    static fn(?Player $player, ?string $parameter): string => 'Example'
);
```

Custom placeholders can be used in messages and other supported formatting contexts.

For parameterized or player-dependent placeholders, consult the API method signature and PlaceholderAPI documentation.
PlaceholderAPI can also be used to register placeholders.

### API Reference

| Method | Description |
|---|---|
| `getManager()` | Returns the centralized enchantment manager. |
| `registerAll()` | Registers multiple enchantment definitions. |
| `check()` | Checks whether an enchantment can be applied. |
| `apply()` | Applies an enchantment to an item. |
| `getEnchants()` | Retrieves enchantments and levels from an item. |
| `registerRarity()` | Registers a custom rarity. |
| `getWornLevels()` | Gets enchantment levels across armor slots. |
| `getTotalWornLevel()` | Gets the combined equipped armor level. |
| `registerMessages()` | Registers addon messages. |
| `sendMessage()` | Sends a formatted message. |
| `sendPopup()` | Sends a formatted popup. |
| `formatMessage()` | Formats a message or raw text. |
| `registerPlaceholder()` | Registers a custom PlaceholderAPI placeholder. |

---

## Contributing

Contributions, bug reports, and feature suggestions are welcome.

If you want to contribute:

1. Fork the repository.
2. Create a branch for your changes.
3. Follow the existing project structure and coding conventions.
4. Test your changes on a PocketMine-MP API 5 server.
5. Submit a pull request describing your changes.

For addon developers, consider distributing your enchantments as separate plugins rather than modifying the main CustomEnchants repository.

---

## Credits

CustomEnchants was originally based on **[ATEnchants](https://github.com/Prim69/ATEnchants)** by Prim69.

Additional libraries and frameworks used by this project are credited to their respective authors.

---

## License

CustomEnchants is licensed under the **GNU General Public License v3.0**.

See [LICENSE](LICENSE) for the full license text.

---

## Links

- [GitHub Repository](https://github.com/pupggers/CustomEnchants)
- [GitHub Releases](https://github.com/pupggers/CustomEnchants/releases)
- [Issue Tracker](https://github.com/pupggers/CustomEnchants/issues)

**Developed by pupggers/dre.**
