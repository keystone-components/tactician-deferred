<?php

declare(strict_types=1);

namespace Keystone\Tactician\Deferred\Worker;

use Keystone\Tactician\Deferred\Command\DeferrableCommand;
use Keystone\Tactician\Deferred\Command\DeferredCommand;
use League\Tactician\CommandBus;
use Mockery;
use PHPUnit\Framework\TestCase;

class DeferrableCommandWorkerTest extends TestCase
{
    public function testHandlesTheCommand()
    {
        $commandBus = Mockery::mock(CommandBus::class);
        $worker = new DeferrableCommandWorker($commandBus);

        // Create a deferrable command
        $command = new class() implements DeferrableCommand {
            public function getKey(): string
            {
                return 'test';
            }
        };

        // Set the expecation that the command is wrapped before handling
        $commandBus->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (DeferredCommand $deferredCommand) use ($command) {
                return $deferredCommand->getCommand() === $command;
            }));

        $worker->process($command);
    }
}
