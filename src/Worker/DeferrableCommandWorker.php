<?php

declare(strict_types=1);

namespace Keystone\Tactician\Deferred\Worker;

use Keystone\Tactician\Deferred\Command\DeferrableCommand;
use Keystone\Tactician\Deferred\Command\DeferredCommand;
use League\Tactician\CommandBus;

/**
 * A queue worker that will delegate the handling of the command to the command bus.
 */
class DeferrableCommandWorker
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param DeferrableCommand $command
     */
    public function process(DeferrableCommand $command)
    {
        // Wrap the deferrable command so the command bus does not publish other queue message
        $this->commandBus->handle(new DeferredCommand($command));
    }
}
