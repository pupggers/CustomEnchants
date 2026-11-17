<?php

declare(strict_types=1);

namespace pup\customenchants;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pup\customenchants\types\BowEnchant;
use pup\customenchants\types\DefenseArmorEnchant;
use pup\customenchants\types\DurabilityEnchant;
use pup\customenchants\types\InteractEnchant;
use pup\customenchants\types\ProjectileEnchant;
use pup\customenchants\types\RandomArmorEnchant;
use pup\customenchants\types\SoulboundEnchant;
use pup\customenchants\types\ToggledArmorEnchant;
use pup\customenchants\types\ToolEnchant;

class EnchantListener implements Listener
{
	/**
	 * @param InventoryTransactionEvent $event
	 * @priority HIGHEST
	 */
	public function onArmorChange(InventoryTransactionEvent $event)
	: void
	{
		ToggledArmorEnchant::onToggle($event);
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority HIGHEST
	 */
	public function onDamage(EntityDamageEvent $event)
	: void
	{
		if ($event instanceof EntityDamageByChildEntityEvent) {
			ProjectileEnchant::onHitEntity($event);
		}
		DefenseArmorEnchant::onDamage($event);
		if ($event->isCancelled()) {
			return;
		}
		RandomArmorEnchant::onDamage($event);
	}

	/**
	 * @param EntityShootBowEvent $event
	 * @priority HIGHEST
	 */
	public function onShoot(EntityShootBowEvent $event)
	: void
	{
		BowEnchant::onShoot($event);
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event)
	: void
	{
		if ($event->isCancelled()) {
			return;
		}
		ToolEnchant::onBreak($event);
	}

	/**
	 * @param ProjectileHitBlockEvent $event
	 * @priority HIGHEST
	 */
	public function onBlockHit(ProjectileHitBlockEvent $event)
	: void
	{
		BowEnchant::onHitBlock($event);
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGHEST
	 */

	public function onJoin(PlayerJoinEvent $event)
	: void
	{
		$player = $event->getPlayer();
		ToggledArmorEnchant::ensureArmorListener($player);
		DurabilityEnchant::ensureListeners($player);
		SoulboundEnchant::restore($player);
		foreach ($player->getArmorInventory()->getContents() as $item) {
			foreach ($item->getEnchantments() as $enchantment) {
				$enchantment = $enchantment->getType();
				if ($enchantment instanceof ToggledArmorEnchant) {
					$enchantment->onEquip($player, $item, $item->getEnchantmentLevel($enchantment) );
				}
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority HIGHEST
	 */
	public function onDeath(PlayerDeathEvent $event)
	: void
	{
		SoulboundEnchant::onDeath($event);
		$player = $event->getPlayer();
		foreach ($player->getArmorInventory()->getContents() as $item) {
			foreach ($item->getEnchantments() as $enchantment) {
				$enchantment = $enchantment->getType();
				if ($enchantment instanceof ToggledArmorEnchant) {
					$enchantment->onDequip($player, $item, $item->getEnchantmentLevel($enchantment));
				}
			}
		}
		ToggledArmorEnchant::removeArmorListener($player);
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @priority HIGHEST
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		ToggledArmorEnchant::removeArmorListener($player);
		DurabilityEnchant::removeListeners($player);
	}

	/**
	 * @param PlayerRespawnEvent $event
	 * @priority HIGHEST
	 */
	public function onRespawn(PlayerRespawnEvent $event): void
	{
		SoulboundEnchant::restore($event->getPlayer());
	}

	/**
	 * Right-click on a block with an enchanted item (planters, launch...).
	 *
	 * @param PlayerInteractEvent $event
	 * @priority HIGHEST
	 */
	public function onInteract(PlayerInteractEvent $event): void
	{
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
			return;
		}
		InteractEnchant::onInteract($event->getPlayer(), $event->getItem(), $event->getBlock());
	}

	/**
	 *
	 * @param PlayerItemUseEvent $event
	 * @priority HIGHEST
	 */
	public function onItemUse(PlayerItemUseEvent $event): void
	{
		InteractEnchant::onInteract($event->getPlayer(), $event->getItem(), null);
	}
}