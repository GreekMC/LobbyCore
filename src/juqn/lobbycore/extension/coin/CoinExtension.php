<?php

declare(strict_types=1);

namespace juqn\lobbycore\extension\coin;

use juqn\economy\session\SessionManager;
use juqn\lobbycore\extension\Extension;
use juqn\lobbycore\LobbyCore;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class CoinExtension extends Extension {
	use SingletonTrait;

	public function getCurrentCoins(Player $player) : string {
		if ($this->enabled) {
			$session = SessionManager::getInstance()->getSession($player);

			if ($session !== null) return '' . floor($session->getBalance());
		}
		return TextFormat::colorize('&cNo plugin available');
	}

	public function increaseCurrentCoins(Player $player, int $coins) : bool {
		if ($this->enabled) {
			$session = SessionManager::getInstance()->getSession($player);

			if ($session !== null) {
				$session->increaseBalance($coins);
				return true;
			}
		}
		return false;
	}

	public function load() : void {
		if (LobbyCore::getInstance()->getServer()->getPluginManager()->getPlugin('Coins') !== null) {
			$this->enabled = true;

			LobbyCore::getInstance()->getLogger()->info('Coins extension has been enabled');
		}
	}

	public function save() : void {}
}