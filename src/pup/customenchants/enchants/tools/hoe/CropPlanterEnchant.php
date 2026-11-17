<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\tools\hoe;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Farmland;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pup\customenchants\types\InteractEnchant;

abstract class CropPlanterEnchant extends InteractEnchant
{
    abstract protected function seed(): Item;

    abstract protected function crop(): Block;

    public function execute(Player $player, Item $item, ?Block $block, int $level): void
    {
        if (!$player->isSneaking() || !$block instanceof Farmland) {
            return;
        }
        $radius = $level >= 3 ? 2 : 1;
        $origin = $block->getPosition();
        $world = $origin->getWorld();
        $inventory = $player->getInventory();
        $seed = $this->seed();

        for ($dx = -$radius; $dx <= $radius; $dx++) {
            for ($dz = -$radius; $dz <= $radius; $dz++) {
                $x = $origin->getFloorX() + $dx;
                $y = $origin->getFloorY();
                $z = $origin->getFloorZ() + $dz;
                if (!$world->getBlockAt($x, $y, $z) instanceof Farmland
                    || $world->getBlockAt($x, $y + 1, $z)->getTypeId() !== BlockTypeIds::AIR) {
                    continue;
                }
                if (!$inventory->contains($seed)) {
                    return; // out of seeds
                }
                $inventory->removeItem($seed);
                $world->setBlockAt($x, $y + 1, $z, $this->crop());
            }
        }
    }
}
