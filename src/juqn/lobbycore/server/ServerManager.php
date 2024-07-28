<?php

declare(strict_types=1);

namespace juqn\lobbycore\server;

use juqn\lobbycore\util\server\query\ServerQueryTask;
use juqn\lobbycore\LobbyCore;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

final class ServerManager
{
    use SingletonTrait;

    /** @var Server[] */
    private array $servers = [];

    public function __construct()
    {
        LobbyCore::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            LobbyCore::getInstance()->getServer()->getAsyncPool()->submitTask(new ServerQueryTask());
        }), (int) LobbyCore::getInstance()->getConfig()->get('server-tick.update'));
    }

    public function getServers(): array
    {
        return $this->servers;
    }

    public function get(string $serverName): ?Server
    {
        return $this->servers[$serverName] ?? null;
    }

    public function load(): void
    {
        $config = new Config(LobbyCore::getInstance()->getDataFolder() . 'servers.yml', Config::YAML);

        foreach ($config->getAll() as $name => $data) {
            if (!isset($data['address']) || !isset($data['prefix'])) {
                return;
            }

            if (isset($data['item'])) {
                try {
                    $item = LegacyStringToItemParser::getInstance()->parse($data['item']);
                } catch (LegacyStringToItemParserException) {
                }
            }
            $this->servers[$name] = new Server($data['address'], intval($data['port'] ?? 19132), $data['prefix'], $data['lore'] ?? [], $item ?? null);
        }
    }
}
