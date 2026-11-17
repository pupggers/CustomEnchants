<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\hoe;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class PlanterEnchant extends CropPlanterEnchant
{
    protected function seed(): Item
    {
        return VanillaItems::WHEAT_SEEDS();
    }

    protected function crop(): Block
    {
        return VanillaBlocks::WHEAT();
    }
}
