<?php

declare(strict_types=1);

namespace pup\customenchants\types;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pup\customenchants\CustomEnchantInterface;
use pup\customenchants\utils\CustomEnchantTrait;

abstract class WeaponEnchant extends MeleeWeaponEnchantment implements CustomEnchantInterface
{
	use CustomEnchantTrait;

	public function isApplicableTo(Entity $victim): bool
	{
		return $victim instanceof Living;
	}

	public function getDamageBonus(int $enchantmentLevel): float
	{
		return 0.0;
	}
}