# Changelog

All notable changes to CustomEnchants are documented in this file.

## [2.0.0]

### Added

- Introduced a centralized enchantment registry shared by built-in enchantments and third-party addons.
- Added `CustomEnchantsAPI` for addon developers.
- Added `EnchantDefinition` for defining and registering custom enchantments.
- Added `ApplyResult` and `ApplyStatus` for structured enchantment application results.
- Added support for configurable enchantment conflicts.
- Added support for enchantment requirements and minimum required levels.
- Added support for enchantment replacement relationships for upgrade paths.
- Added a centralized enchantment validation and application system through `check()` and `apply()`.
- Added support for custom enchantment rarity registration.
- Added persistent enchantment ID assignments through `enchant_ids.json`.
- Added validation and automatic reassignment for unsafe or conflicting enchantment IDs.
- Added configurable enchantment lore formatting.
- Added addon message registration and custom PlaceholderAPI placeholders.
- Added addon installation through GitHub releases.
- Added addon installation manifests containing repository, release, and checksum information.
- Added automatic resolution of supported addon dependencies.
- Added support for addon installation from the server console.
- Added addon listing and removal commands.
- Added configurable addon repository aliases.

### Changed

- Reworked enchantment registration to support both built-in enchantments and independently developed addons.
- Standardized enchantment application through the centralized manager.
- Improved enchantment conflict handling.
- Enchantment conflicts now prevent application instead of silently replacing existing enchantments.
- Improved Heroic upgrade requirements and replacement handling.
- Updated enchantment configuration to support `conflicts`, `requires`, and `replaces`.
- Improved enchantment configuration handling and defaults.
- Improved enchantment ID allocation to reduce collisions with other registered enchantments.
- Improved enchantment message formatting through PlaceholderAPI.
- Expanded the main configuration to support custom rarities, shop settings, book chances, and addon management.

### Developer API

- Added `CustomEnchantsAPI::getManager()`.
- Added `CustomEnchantsAPI::registerAll()`.
- Added `CustomEnchantsAPI::check()`.
- Added `CustomEnchantsAPI::apply()`.
- Added `CustomEnchantsAPI::getEnchants()`.
- Added `CustomEnchantsAPI::registerRarity()`.
- Added `CustomEnchantsAPI::getWornLevels()`.
- Added `CustomEnchantsAPI::getTotalWornLevel()`.
- Added `CustomEnchantsAPI::registerMessages()`.
- Added `CustomEnchantsAPI::sendMessage()`.
- Added `CustomEnchantsAPI::sendPopup()`.
- Added `CustomEnchantsAPI::formatMessage()`.
- Added `CustomEnchantsAPI::registerPlaceholder()`.

---

## Versioning

CustomEnchants follows semantic versioning:

- **MAJOR** – Breaking changes to functionality, configuration, or the developer API.
- **MINOR** – New features and backwards-compatible improvements.
- **PATCH** – Bug fixes and backwards-compatible corrections.

## Links

- [Repository](https://github.com/pupggers/CustomEnchants)
- [Releases](https://github.com/pupggers/CustomEnchants/releases)
- [Issues](https://github.com/pupggers/CustomEnchants/issues)
