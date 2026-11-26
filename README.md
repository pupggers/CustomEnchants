# CustomEnchants

A PocketMine-MP plugin that adds powerful custom enchantments to your server.

## Usage
- `/ce add <enchant> [level] [force]` - Apply an enchant to the item you're holding. `force` skips conflict/requirement checks.
- `/ce remove <enchant>` - Remove an enchant from the item you're holding
- `/ce test <1|2|3|4>` - Spawn test chests: coverage, conflicts (with an API self-check), synergies, interactions
- `/ce addon <list | install <name> | remove <name>>` - Manage addon packs (works from the console)

## Conflicts, requirements, Heroic upgrades
Each enchant in `enchantments.json` can set the same relations as AdvancedEnchantments:

| key         | AE name              | meaning                                                      |
|-------------|----------------------|--------------------------------------------------------------|
| `conflicts` | `not-applyable-with` | can't share an item; applying one is refused with an error   |
| `requires`  | `required-enchants`  | `{"heavy": 5}` = Heavy V must already be on the item         |
| `replaces`  | `removed-enchants`   | removed when this one is applied (Heroic upgrades only)      |

A clash never replaces anything: the book / command fails and the item is left alone.
Built-in clashes are in `DefaultEnchants::CONFLICT_GROUPS`; setting `conflicts` in the JSON overrides them per enchant.

## Addons
Addons are separate plugin phars, not bundled into this phar. List trusted GitHub
repositories under `addons.repositories` in `config.yml`, then run `/ce addon install <name>`: the latest
release's `.phar` is downloaded into `plugin_data/CustomEnchant/addons/` and loaded. Addons register
their enchants through `CustomEnchantsAPI::registerAll()`.

If an addon's `plugin.yml` lists a `depend` that isn't loaded (e.g. Cosmic and Fabled need `CustomEnchantsSouls`),
the installer fetches it first, from a `repositories` entry whose alias or repo name matches the plugin name,
then loads the addon. `softdepend` entries are left alone.

## Messages
Every message is a key under `messages:` in `config.yml`, rendered through PlaceholderAPI (colors, `{player}`,
`{server_online}`, `{enchant_name:<key>}`...). Set one to `""` to turn it off. PlaceholderAPI is shaded into this phar,
so addons use it through the API: `CustomEnchantsAPI::registerMessages($this, $defaults)` (owners override them in the
addon's `config.yml` or here), `sendMessage()`, `sendPopup()`, `formatMessage()` and `registerPlaceholder()`.
The Souls addon adds `{souls}` and `{souls:<player>}`.
