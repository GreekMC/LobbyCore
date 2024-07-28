<?php

declare(strict_types=1);

namespace juqn\lobbycore\util\server\query;

use juqn\lobbycore\server\Server;
use juqn\lobbycore\server\ServerManager;
use juqn\lobbycore\util\server\ServerMenu;
use juqn\lobbycore\util\server\ServerState;
use pocketmine\scheduler\AsyncTask;
use pmmp\thread\ThreadSafeArray;

final class ServerQueryTask extends AsyncTask
{
    private ThreadSafeArray $servers;

    public function __construct()
    {
        $servers = array_map(fn (Server $server) => $server->serialize(), ServerManager::getInstance()->getServers());
        $this->servers = ThreadSafeArray::fromArray($servers);
    }

    public function onRun(): void
    {
        $data = [];

        foreach ($this->servers as $name => $server) {
            $result = ServerQuery::query($server['address'], $server['port']);

            if ($result !== null) {
                $data[$name] = $result;
            }
        }
        $this->setResult($data);
    }

    public function onCompletion(): void
    {
        $result = $this->getResult();

        if ($result !== null) {
            $needUpdate = false;

            foreach ($result as $serverName => $serverData) {
                $server = ServerManager::getInstance()->get($serverName);

                if ($server === null) {
                    continue;
                }

                if ($serverData['result'] === 'error') {
                    $result = $server->update(ServerState::OFFLINE());
                } else {
                    $state = $serverData['whitelist'] === 'off' ? ServerState::ONLINE() : ServerState::WHITELIST();
                    $players = (int) $serverData['numplayers'];
                    $max_players = (int) $serverData['maxplayers'];
                    $whitelist_players = explode(', ', $serverData['whitelist_players'] ?? '');
                    $op_players = explode(', ', $serverData['op_players'] ?? '');
                    $list_players = array_values(explode(', ', $serverData['list_players'] ?? ''));

                    $result = $server->update($state, $players, $max_players, $whitelist_players, $op_players, $list_players);
                }

                if (!$needUpdate && $result) {
                    $needUpdate = true;
                }
            }
            if ($needUpdate) {
                ServerMenu::getInstance()->update();
            }
        }
    }
}
