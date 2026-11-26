<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

trait CustomEnchantTrait
{
    use ChanceTrait;

    private string $description;

    public function __construct(
        string $name,
        int $rarity,
        string $description,
        int $maxLevel
    ) {
        $this->description = $description;
        parent::__construct($name, $rarity, 0, 0, $maxLevel);
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
