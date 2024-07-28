<?php

declare(strict_types=1);

namespace juqn\lobbycore\profile;

use juqn\lobbycore\extension\coin\CoinExtension;
use juqn\lobbycore\extension\rank\RankExtension;
use juqn\lobbycore\server\ServerManager;
use juqn\lobbycore\server\Server;
use juqn\lobbycore\util\Scoreboard;
use juqn\lobbycore\util\server\ServerState;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class Profile
{
    private readonly Scoreboard $scoreboard;

    public function __construct(
        private readonly Player $player
    ) {
        $this->scoreboard = new Scoreboard($this->player);

        $this->player->getInventory()->clearAll();
        $this->player->getArmorInventory()->clearAll();

        $this->player->getEffects()->clear();

        $this->player->getHungerManager()->setFood(20);
        $this->player->getHungerManager()->setEnabled(false);

        $this->player->setHealth(20);

        $this->player->getInventory()->setItem(4, VanillaItems::DIAMOND_SWORD()->setCustomName(TextFormat::colorize('&r&5Servers')));
        $this->player->getInventory()->setItem(8, VanillaItems::ENDER_PEARL()->setCustomName(TextFormat::colorize('&r&3Enderbutt')));

        $this->player->teleport($this->player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
    }

    public function __destroy()
    {
        // remove queue.

    }

    public function update(): void
    {
        $this->updateScoreboard();
    }

    protected function updateScoreboard(): void
    {
        $player = $this->player;

        if (!$player->isOnline()) {
            return;
        }
        $current_players = count($this->player->getServer()->getOnlinePlayers());
        $current_max_players = $this->player->getServer()->getMaxPlayers();

        $servers = array_filter(ServerManager::getInstance()->getServers(), fn (Server $server) => !$server->getState()->equals(ServerState::OFFLINE()));

        $players = $current_players + intval(array_sum(array_map(fn (Server $server) => $server->getPlayers(), $servers)));
        $max_players = $current_max_players + intval(array_sum(array_map(fn (Server $server) => $server->getMaxPlayers(), $servers)));

        $lines = LobbyCore::getInstance()->getConfig()->get('scoreboard-lines');
        $lines = array_map(fn (string $line) => str_replace(['{players}', '{max_players}', '{rank}', '{coins}', '{date}'], [$players, $max_players, RankExtension::getInstance()->getCurrentRank($this->player), CoinExtension::getInstance()->getCurrentCoins($this->player), date('j/n/Y | G:i:s A')], $line), $lines);

        $this->scoreboard->respawn();
        $this->scoreboard->setLines($lines);
    }
}
