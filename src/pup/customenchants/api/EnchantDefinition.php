<?php

declare(strict_types=1);

namespace pup\customenchants\api;

use Closure;
use InvalidArgumentException;
use pocketmine\item\enchantment\Enchantment;
use pup\customenchants\CustomEnchantInterface;
use pup\customenchants\EnchantManager;

final class EnchantDefinition
{
	public readonly string $key;

	/** @var list<string> */
	public readonly array $tags;

	/** @var list<string> */
	public readonly array $conflicts;

	/** @var array<string, int> */
	public readonly array $requires;

	/** @var list<string> */
	public readonly array $replaces;

	/**
	 * @param list<string> $tags
	 * @param Closure(string $name, int $rarity, string $description, int $maxLevel): (Enchantment&CustomEnchantInterface) $factory
	 * @param list<string> $conflicts
	 * @param array<string, int>|list<string> $requires
	 * @param list<string> $replaces
	 * @param array<string, mixed> $defaults
	 */
	public function __construct(
		string $key,
		public readonly int $id,
		array $tags,
		public readonly Closure $factory,
		array $conflicts = [],
		array $requires = [],
		array $replaces = [],
		public readonly array $defaults = [],
	) {
		$this->key = EnchantManager::normalizeKey($key);
		if ($this->key === '') {
			throw new InvalidArgumentException('Enchant key cannot be empty.');
		}
		$tags = array_values(array_unique(array_filter($tags, static fn(mixed $t): bool => is_string($t) && $t !== '')));
		if ($tags === []) {
			throw new InvalidArgumentException("Enchant '$this->key' needs at least one ItemEnchantmentTags tag.");
		}
		$this->tags = $tags;
		$this->conflicts = self::keys($conflicts);
		$this->requires = self::requirements($requires);
		$this->replaces = self::keys($replaces);
	}

	/** @param array<string, mixed> $data */
	public function withConfig(array $data): self
	{
		return new self(
			$this->key,
			$this->id,
			$this->tags,
			$this->factory,
			is_array($data['conflicts'] ?? null) ? $data['conflicts'] : $this->conflicts,
			is_array($data['requires'] ?? null) ? $data['requires'] : $this->requires,
			is_array($data['replaces'] ?? null) ? $data['replaces'] : $this->replaces,
			$this->defaults,
		);
	}

	/**
	 * @param array<mixed> $list
	 * @return list<string>
	 */
	private static function keys(array $list): array
	{
		$out = [];
		foreach ($list as $value) {
			if (is_string($value) && ($key = EnchantManager::normalizeKey($value)) !== '') {
				$out[$key] = $key;
			}
		}
		return array_values($out);
	}

	/**
	 * @param array<mixed> $list
	 * @return array<string, int>
	 */
	private static function requirements(array $list): array
	{
		$out = [];
		foreach ($list as $key => $value) {
			if (is_string($key)) {
				$name = $key;
				$level = is_numeric($value) ? (int) $value : 1;
			} elseif (is_string($value)) {
				[$name, $level] = array_pad(explode(':', $value, 2), 2, '1');
				$level = (int) $level;
			} else {
				continue;
			}
			$name = EnchantManager::normalizeKey($name);
			if ($name !== '') {
				$out[$name] = max(1, $level);
			}
		}
		return $out;
	}
}
