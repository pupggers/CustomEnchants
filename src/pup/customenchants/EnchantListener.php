<?php

declare(strict_types=1);

namespace pup\customenchants;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
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
use pocketmine\player\Player;
use pup\customenchants\types\BowEnchant;
use pup\customenchants\types\DefenseArmorEnchant;
use pup\customenchants\types\DurabilityEnchant;
use pup\customenchants\types\InteractEnchant;
use pup\customenchants\types\ProjectileEnchant;
use pup\customenchants\types\RandomArmorEnchant;
use pup\customenchants\types\SoulboundEnchant;
use pup\customenchants\types\ToggledArmorEnchant;
use pup\customenchants\types\ToolEnchant;
use pup\customenchants\utils\ArmorEffects;
use pup\customenchants\utils\HelmetSwap;
use pup\customenchants\utils\EnchantTasks;

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
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority HIGHEST
	 */
	public function onDeath(PlayerDeathEvent $event)
	: void
	{
		HelmetSwap::onDeath($event);
		SoulboundEnchant::onDeath($event);
		ToggledArmorEnchant::removeArmorListener($event->getPlayer());
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @priority HIGHEST
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		HelmetSwap::restore($player);
		ToggledArmorEnchant::removeArmorListener($player);
		DurabilityEnchant::removeListeners($player);
		ArmorEffects::forget($player);
	}

	/**
	 * @param PlayerRespawnEvent $event
	 * @priority HIGHEST
	 */
	public function onRespawn(PlayerRespawnEvent $event): void
	{
		$player = $event->getPlayer();
		SoulboundEnchant::restore($player);
		// Respawning clears effects after this event, so re-equip armor enchants a tick later.
		EnchantTasks::later(1, static function () use ($player): void {
			if ($player->isConnected() && $player->isAlive()) {
				ToggledArmorEnchant::ensureArmorListener($player);
			}
		});
	}

	/** @priority MONITOR */
	public function onEffectRemove(EntityEffectRemoveEvent $event): void
	{
		$player = $event->getEntity();
		$effect = $event->getEffect()->getType();
		if (!$player instanceof Player || !ArmorEffects::isTracked($player, $effect)) {
			return;
		}
		EnchantTasks::later(1, static function () use ($player, $effect): void {
			if ($player->isConnected() && $player->isAlive()) {
				ArmorEffects::refresh($player, $effect);
			}
		});
	}

	/**
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
	 * @param PlayerItemUseEvent $event
	 * @priority HIGHEST
	 */
	public function onItemUse(PlayerItemUseEvent $event): void
	{
		InteractEnchant::onInteract($event->getPlayer(), $event->getItem(), null);
	}
}