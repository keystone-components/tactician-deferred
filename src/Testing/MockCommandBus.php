<?php

namespace Keystone\Tactician\Deferred\Testing;

use League\Tactician\CommandBus;

class MockCommandBus extends CommandBus
{
    public $handled = [];

    public function __construct(array $middleware = [])
    {
    }

    public function handle($command)
    {
        $this->handled[] = $command;
    }
}
