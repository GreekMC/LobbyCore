<?php

declare(strict_types=1);

namespace juqn\lobbycore\profile;

use juqn\lobbycore\LobbyCore;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

final class ProfileManager
{
    use SingletonTrait;

    /** @var Profile[] */
    private array $profiles = [];

    public function __construct()
    {
        LobbyCore::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->profiles as $session) {
                $session->update();
            }
        }), 5);
    }

    public function get(Player $player): ?Profile
    {
        return $this->profile[$player->getXuid()] ?? null;
    }

    public function create(Player $player): void
    {
        $this->profiles[$player->getXuid()] = new Profile($player);
    }

    public function remove(Player $player): void
    {
        if (!isset($this->profiles[$player->getXuid()])) {
            return;
        }
        unset($this->profiles[$player->getXuid()]);
    }
}
