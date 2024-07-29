<?php

declare(strict_types=1);

namespace juqn\lobbycore\extension\rank;

use juqn\lobbycore\extension\Extension;
use juqn\lobbycore\LobbyCore;
use juqn\ranks\profile\ProfileManager;
use juqn\ranks\util\RankInfo;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class RankExtension extends Extension
{
    use SingletonTrait;

    public function getCurrentRank(Player $player): string
    {
        if ($this->enabled) {
            $session = ProfileManager::getInstance()->get($player);

            if ($session !== null) {
                $ranks = array_filter($session->getRanks(), fn(RankInfo $info) => !$info->isExpired());
                
                if (count($ranks) === 0) return '&7Guest';
                return implode(' ', array_map(fn(RankInfo $rankInfo) => $rankInfo->getRank()->getFormat(), array_values($ranks)));
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
