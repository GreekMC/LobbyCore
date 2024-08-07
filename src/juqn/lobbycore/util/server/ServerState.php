<?php

declare(strict_types=1);

namespace juqn\lobbycore\util\server;

use pocketmine\utils\EnumTrait;
use pocketmine\utils\TextFormat;

/**
 * @method static ServerState OFFLINE
 * @method static ServerState ONLINE
 * @method static ServerState WHITELIST
 */
final class ServerState {
	use EnumTrait {
		__construct as Enum__construct;
	}

	protected static function setup() : void {
		self::register(new ServerState('offline', '&r&cOFFLINE'));
		self::register(new ServerState('online', '&r&aONLINE'));
		self::register(new ServerState('whitelist', '&r&eWHITELIST'));
	}

	public function __construct(string $enumName, private readonly string $prefix) {
		$this->Enum__construct($enumName);
	}

	public function getPrefix() : string {
		return TextFormat::colorize($this->prefix);
	}
}