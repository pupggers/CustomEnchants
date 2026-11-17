<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

use pocketmine\item\enchantment\ItemFlags;

trait CustomEnchantTrait
{
    use ChanceTrait;

    private string $description;

    public function __construct(
        string $name,
        int $rarity,
        string $description,
        int $maxLevel,
        int $primaryFlag,
        int $secondaryFlag = ItemFlags::NONE
    ) {
        $this->description = $description;
        parent::__construct($name, $rarity, $primaryFlag, $secondaryFlag, $maxLevel);
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
