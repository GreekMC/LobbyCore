<?php

declare(strict_types=1);

namespace juqn\lobbycore\server;

use juqn\lobbycore\util\server\ServerState;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

final class Server {
	private int $players = 0;
	private int $max_players = 0;

	private array $whitelist_players = [];
	private array $op_players = [];

	private array $list_players = [];

	private ServerState $state;

	public function __construct(private readonly string $address, private readonly int $port, private readonly string $prefix, private readonly array $lore, private readonly ?Item $item) {
		$this->state = ServerState::OFFLINE();
	}

	public function getAddress() : string {
		return $this->address;
	}

	public function getPort() : int {
		return $this->port;
	}

	public function getPrefix() : string {
		return TextFormat::colorize($this->prefix);
	}

	public function getLore() : array {
		return $this->lore;
	}

	public function getItem() : Item {
		return $this->item ?? VanillaItems::BOOK();
	}

	public function getPlayers() : int {
		return $this->players;
	}

	public function getMaxPlayers() : int {
		return $this->max_players;
	}

	public function getPlayerList() : array {
		return $this->list_players;
	}

	public function getPlayerWhitelisted() : array {
		return $this->whitelist_players;
	}

	public function getPlayerOps() : array {
		return $this->op_players;
	}

	public function getState() : ServerState {
		return $this->state;
	}

	public function update(ServerState $state, ?int $current = null, ?int $max = null, ?array $whitelistPlayers = null, ?array $opPlayers = null, ?array $listPlayers = null) : bool {
		$players = $current ?? $this->players;
		$max_players = $max ?? $this->max_players;
		$whitelist_players = $whitelistPlayers ?? $this->whitelist_players;
		$op_players = $opPlayers ?? $this->op_players;
		$list_players = $listPlayers ?? $this->list_players;

		if ($state->equals(ServerState::OFFLINE()) && $this->state->equals($state)) return false;
		$needUpdate = false;

		if (!$this->state->equals($state)) {
			$this->state = $state;
			$needUpdate = true;
		}

		if ($this->players !== $players) {
			$this->players = $players;
			$needUpdate = true;
		}

		if ($this->max_players !== $max_players) {
			$this->max_players = $max_players;
			$needUpdate = true;
		}

		if (count(array_diff($whitelist_players, $this->whitelist_players)) !== 0 || count(array_diff($this->whitelist_players, $whitelist_players)) !== 0) {
			$this->whitelist_players = $whitelist_players;
			$needUpdate = true;
		}

		if (count(array_diff($op_players, $this->op_players)) !== 0 || count(array_diff($this->op_players, $op_players)) !== 0) {
			$this->op_players = $op_players;
			$needUpdate = true;
		}

		if (count(array_diff($list_players, $this->list_players)) !== 0 || count(array_diff($this->list_players, $list_players)) !== 0) {
			$this->list_players = $list_players;
			$needUpdate = true;
		}
		return $needUpdate;
	}

	public function serialize() : array {
		return ['address' => $this->address, 'port' => $this->port];
	}
}