<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Living;

final class ArmorEffects
{
	private const int INFINITE = 2147483647;

	/** @var array<int, array<int, array{effect: Effect, sources: array<string, int>, applied: ?int}>> */
	private static array $effects = [];

	/** @var array<int, array<string, int>> */
	private static array $health = [];

	public static function grant(Living $player, string $source, Effect $effect, int $amplifier): void
	{
		$entry = &self::$effects[$player->getId()][spl_object_id($effect)];
		$entry ??= ['effect' => $effect, 'sources' => [], 'applied' => null];
		$entry['sources'][$source] = max(0, $amplifier);
		unset($entry);
		self::refresh($player, $effect);
	}

	public static function revoke(Living $player, string $source, Effect $effect): void
	{
		$id = spl_object_id($effect);
		if (!isset(self::$effects[$player->getId()][$id])) {
			return;
		}
		unset(self::$effects[$player->getId()][$id]['sources'][$source]);
		self::refresh($player, $effect);
	}

	public static function refresh(Living $player, Effect $effect): void
	{
		$id = spl_object_id($effect);
		$entry = self::$effects[$player->getId()][$id] ?? null;
		if ($entry === null) {
			return;
		}

		$effects = $player->getEffects();
		$current = $effects->get($effect);
		$ours = $current !== null && $current->isInfinite() && $current->getAmplifier() === $entry['applied'];

		if ($entry['sources'] === []) {
			if ($ours) {
				$effects->remove($effect);
			}
			unset(self::$effects[$player->getId()][$id]);
			return;
		}

		$amplifier = max($entry['sources']);
		if ($ours && $current->getAmplifier() === $amplifier) {
			return;
		}
		if ($current !== null && !$ours && $current->getAmplifier() >= $amplifier) {
			return;
		}
		if ($current !== null) {
			$effects->remove($effect);
		}
		if ($effects->add(new EffectInstance($effect, self::INFINITE, $amplifier, false, false, null, true))) {
			self::$effects[$player->getId()][$id]['applied'] = $amplifier;
		}
	}

	public static function isTracked(Living $player, Effect $effect): bool
	{
		return (self::$effects[$player->getId()][spl_object_id($effect)]['sources'] ?? []) !== [];
	}

	public static function setHealthBonus(Living $player, string $source, int $bonus): void
	{
		$old = self::$health[$player->getId()][$source] ?? 0;
		if ($old === $bonus) {
			return;
		}
		if ($bonus === 0) {
			unset(self::$health[$player->getId()][$source]);
		} else {
			self::$health[$player->getId()][$source] = $bonus;
		}
		$player->setMaxHealth(max(1, $player->getMaxHealth() + $bonus - $old));
	}

	public static function forget(Living $player): void
	{
		unset(self::$effects[$player->getId()], self::$health[$player->getId()]);
	}
}
