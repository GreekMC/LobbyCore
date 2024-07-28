<?php

declare(strict_types=1);

namespace juqn\lobbycore\extension;

abstract class Extension
{
    protected bool $enabled = false;

    abstract public function load(): void;
    abstract public function save(): void;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
