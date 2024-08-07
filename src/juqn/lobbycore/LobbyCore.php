<?php

declare(strict_types=1);

namespace juqn\lobbycore;

use juqn\lobbycore\extension\coin\CoinExtension;
use juqn\lobbycore\extension\rank\RankExtension;
use juqn\lobbycore\listener\ProfileListener;
use juqn\lobbycore\listener\ServerListener;
use juqn\lobbycore\server\ServerManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class LobbyCore extends PluginBase {
	use SingletonTrait;

	public function onLoad() : void {
		self::setInstance($this);

		$this->saveDefaultConfig();
		$this->saveResource('servers.yml');
	}

	public function onEnable() : void {
		if (!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}

		ServerManager::getInstance()->load();

		CoinExtension::getInstance()->load();
		RankExtension::getInstance()->load();

		$this->getServer()->getPluginManager()->registerEvents(new ProfileListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ServerListener(), $this);
	}
}