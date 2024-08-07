<?php

declare(strict_types=1);

namespace juqn\lobbycore\util\server;

use juqn\lobbycore\profile\ProfileManager;
use juqn\lobbycore\server\ServerManager;
use juqn\lobbycore\util\server\ServerState;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class ServerMenu {
	use SingletonTrait;

	protected const ROW = 5;
	protected const SERVERS_PER_PAGE = self::ROW;

	protected const FIRST = 0;
	protected const LAST = self::FIRST + self::SERVERS_PER_PAGE;

	private InvMenu $currentMenu;

	public function __construct() {
		$item = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::CYAN())->asItem();
		$this->currentMenu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST)->setName(TextFormat::colorize('&r&e Network Servers '))->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) : void {
				$player = $transaction->getPlayer();
				$item = $transaction->getItemClicked();

				if ($item->getNamedTag()->getTag('serverName') === null) {
					$player->sendMessage(TextFormat::colorize('&cItem no has serverName tag'));
					return;
				}
				$server = ServerManager::getInstance()->get($item->getNamedTag()->getString('serverName'));

				if ($server === null) return;

				if ($server->getState()->equals(ServerState::OFFLINE())) {
					$player->sendMessage(TextFormat::colorize('&cServer is offline.'));
					return;
				}

				if ($server->getState()->equals(ServerState::WHITELIST())) {
					$whitelisted = $server->getPlayerWhitelisted();
					$ops = $server->getPlayerOps();

					if (!in_array(strtolower($player->getName()), $whitelisted) && !in_array(strtolower($player->getName()), $ops)) {
						$player->sendMessage(TextFormat::colorize('&cServer in whitelisted'));
						return;
					}
				}
				$player->transfer($server->getAddress(), $server->getPort());
			}));

		for ($i = 0; $i < 27; $i++) {
			if ($i >= 10 && $i <= 16) continue;
			$this->currentMenu->getInventory()->setItem($i, $item);
		}
		$this->update();
	}

	public function update() : void {
		$data = ServerManager::getInstance()->getServers();
		$names = array_keys($data);
		$servers = array_values($data);

		$slot = 10;

		for ($i = self::FIRST; $i < self::LAST; $i++) {
			$slot++;

			if ($i !== self::FIRST && $i % self::ROW === 0) {
				$slot += 4;
			}
			if (count($servers) <= $i) {
				break;
			}
			$server = $servers[$i];
			$name = $names[$i];

			$prefix = $server->getPrefix();
			$lore = array_map(fn(int $id, string $text) => str_replace(['{players}', '{max_players}', '{status}'], [$server->getState()->equals(ServerState::OFFLINE()) ? '' . 0 : '' . $server->getPlayers(), '' . $server->getMaxPlayers(), $server->getState()->getPrefix()], $this->createLine($id, $text)), array_keys($server->getLore()), array_values($server->getLore()));

			$item = clone $server->getItem();
			$item->setCustomName($prefix);
			$item->setLore($lore);
			$item->getNamedTag()->setString('serverName', $name);

			$this->currentMenu->getInventory()->setItem($slot, $item);
		}
	}

	public function sendTo(Player $player) : void {
		$this->currentMenu->send($player);
	}

	protected function createLine(int $id, string $server) : string {
		return TextFormat::colorize($server) . str_repeat('\0', $id);
	}
}