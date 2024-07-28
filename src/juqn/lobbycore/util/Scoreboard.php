<?php

declare(strict_types=1);

namespace juqn\lobbycore\util;

use juqn\lobbycore\LobbyCore;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class Scoreboard
{
    protected const OBJECTIVE_NAME = 'scoreboard';
    protected const CRITERIA_NAME = 'dummy';

    protected const MAX_LINES = 15;

    public function __construct(
        private readonly Player $player
    ) {
        $this->spawn();
    }

    public function respawn(): void
    {
        $this->despawn();
        $this->spawn();
    }

    public function setLines(array $lines): void
    {
        $player = $this->player;

        if (!$player->isOnline()) {
            return;
        }
        $count = count($lines);

        if ($count > self::MAX_LINES) {
            return;
        }
        $packet = SetScorePacket::create(
            SetScorePacket::TYPE_CHANGE,
            array_map(
                fn (int $id, string $value) => self::createLine($id, $value),
                array_keys($lines),
                array_values($lines)
            )
        );

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    protected function spawn(): void
    {
        $player = $this->player;

        if (!$player->isOnline()) {
            return;
        }
        $titles = LobbyCore::getInstance()->getConfig()->get('scoreboard-titles');
        $title = $titles[(int) (microtime(true) / 0.4) % count($titles)];
        $packet = SetDisplayObjectivePacket::create(
            SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR,
            self::OBJECTIVE_NAME,
            TextFormat::colorize($title),
            self::CRITERIA_NAME,
            SetDisplayObjectivePacket::SORT_ORDER_ASCENDING
        );

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    protected function despawn(): void
    {
        $player = $this->player;

        if (!$player->isOnline()) {
            return;
        }
        $packet = RemoveObjectivePacket::create(
            self::OBJECTIVE_NAME
        );

        $player->getNetworkSession()->sendDataPacket($packet);
    }

    protected function createLine(int $id, string $value): ScorePacketEntry
    {
        $packet = new ScorePacketEntry();
        $packet->objectiveName = self::OBJECTIVE_NAME;
        $packet->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $packet->scoreboardId = $id;
        $packet->score = $id;
        $packet->customName = TextFormat::colorize($value) . str_repeat("\0", $id);

        return $packet;
    }
}
