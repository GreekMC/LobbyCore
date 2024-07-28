<?php

declare(strict_types=1);

namespace juqn\lobbycore\listener;

use juqn\lobbycore\LobbyCore;
use juqn\lobbycore\server\ServerManager;
use juqn\lobbycore\server\Server;
use juqn\lobbycore\util\server\ServerState;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\player\Player;

final class ServerListener implements Listener
{
    public function handleRegenerate(QueryRegenerateEvent $event): void
    {
        $queryInfo = $event->getQueryInfo();

        $currentPlayers = count(LobbyCore::getInstance()->getServer()->getOnlinePlayers());
        $currentPlayerList = array_map(fn (Player $player) => $player->getName(), LobbyCore::getInstance()->getServer()->getOnlinePlayers());

        $servers = array_filter(ServerManager::getInstance()->getServers(), fn (Server $server) => !$server->getState()->equals(ServerState::OFFLINE()));
        $playerList = array_map(fn (Server $server) => $server->getPlayerList(), $servers);

        foreach ($playerList as $server => $list) {
            $currentPlayerList = array_merge($currentPlayerList, $list);
        }
        $queryInfo->setPlayerCount($currentPlayers + intval(array_sum(array_map(fn (Server $server) => $server->getPlayers(), $servers))));
        $queryInfo->setMaxPlayerCount($currentPlayers + intval(array_sum(array_map(fn (Server $server) => $server->getPlayers(), $servers))) + 1);
        $queryInfo->setPlayerList($currentPlayerList);
    }
}
