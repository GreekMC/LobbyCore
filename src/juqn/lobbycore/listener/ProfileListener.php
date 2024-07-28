<?php

declare(strict_types=1);

namespace juqn\lobbycore\listener;

use juqn\lobbycore\profile\ProfileManager;
use juqn\lobbycore\util\server\ServerMenu;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class ProfileListener implements Listener
{
    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Player) {
            return;
        }
        $event->cancel();

        if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
            $entity->teleport($entity->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        switch (TextFormat::clean($item->getCustomName())) {
            case 'Servers':
                $event->cancel();

                ServerMenu::getInstance()->sendTo($player);
                break;

            case 'Enderbutt':
                $event->cancel();

                $player->setMotion($player->getDirectionVector()->multiply(2.8));
                break;
        }
    }

    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();

        ProfileManager::getInstance()->create($player);
    }

    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        ProfileManager::getInstance()->remove($player);
    }
}
