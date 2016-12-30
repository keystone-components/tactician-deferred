<?php

namespace Keystone\Tactician\Deferred;

use Keystone\Tactician\Deferred\Command\DeferrableInterface;

class DeferredCommand
{
    private $command;

    public function __construct(DeferrableInterface $command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        return $this->command;
    }
}
