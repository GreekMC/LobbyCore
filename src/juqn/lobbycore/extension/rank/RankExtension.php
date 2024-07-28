<?php

declare(strict_types=1);

namespace juqn\lobbycore\extension\rank;

use juqn\lobbycore\extension\Extension;
use juqn\lobbycore\LobbyCore;
use juqn\ranks\session\SessionManager;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class RankExtension extends Extension
{
    use SingletonTrait;

    public function getCurrentRank(Player $player): string
    {
        if ($this->enabled) {
            $session = SessionManager::getInstance()->getSession($player);

            if ($session !== null) {
                return TextFormat::colorize($session->getPrimaryRank()->getColor() . $session->getPrimaryRank()->getName());
            }
        }
        return TextFormat::colorize('&cNo plugin available');
    }

    public function load(): void
    {
        if (LobbyCore::getInstance()->getServer()->getPluginManager()->getPlugin('Ranks') !== null) {
            $this->enabled = true;

            LobbyCore::getInstance()->getLogger()->info('Rank extension has been enabled');
        }
    }

    public function save(): void
    {
    }
}
