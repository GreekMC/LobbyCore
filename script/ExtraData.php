<?php

declare(strict_types=1);

/**
 * @name ExtraData
 * @author JuqnGOOD
 * @version 1.0
 * @main juqn\extradata\ExtraData
 * @api 5.0.0
 */

namespace juqn\extradata;

use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

final class ExtraData extends PluginBase implements Listener
{
    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function handleRegenerate(QueryRegenerateEvent $event): void
    {
        $queryInfo = $event->getQueryInfo();
        $extraData = $queryInfo->getExtraData();

        $extraData['whitelist_players'] = implode(', ', array_keys($this->getServer()->getWhitelisted()->getAll()));
        $extraData['op_players'] = implode(', ', array_keys($this->getServer()->getOps()->getAll()));
        $extraData['list_players'] = implode(', ', array_map(fn (Player $player) => $player->getName(), $this->getServer()->getOnlinePlayers()));

        $queryInfo->setExtraData($extraData);
    }
}
