<?php

declare(strict_types=1);

namespace pup\customenchants;

use pocketmine\item\enchantment\Enchantment;
use pup\customenchants\utils\CustomEnchantTrait;

abstract class CustomEnchant extends Enchantment implements CustomEnchantInterface
{
    use CustomEnchantTrait;
}