<?php

declare(strict_types=1);

namespace Keystone\Tactician\Deferred;

use Keystone\Queue\Publisher\FakePublisher;
use Keystone\Tactician\Deferred\Command\DeferrableCommand;
use Keystone\Tactician\Deferred\Command\DeferredCommand;
use PHPUnit\Framework\TestCase;

class DeferredMiddlewareTest extends TestCase
{
    private $publisher;
    private $middleware;

    public function setUp()
    {
        $this->publisher = new FakePublisher();
        $this->middleware = new DeferredMiddleware($this->publisher);
    }

    public function testPublishesDeferrableCommandToQueue()
    {
        $command = new class() implements DeferrableCommand {
            public function getKey(): string
            {
                return 'test';
            }
        };

        $next = $this->createNext();

        $this->middleware->execute($command, $next);

        // The next callable should not be called
        $this->assertNull($next->command);

        // The command should be published as a message to the queue
        $this->assertSame($command, $this->publisher->getMessages()[0]);
    }

    public function testHandlesWrappedDeferredCommand()
    {
        $command = new class() implements DeferrableCommand {
            public function getKey(): string
            {
                return 'test';
            }
        };

        $deferredCommand = new DeferredCommand($command);
        $next = $this->createNext();

        $this->middleware->execute($deferredCommand, $next);

        // The next callable should be called with the inner command
        $this->assertSame($command, $next->command);
    }

    public function testHandlesWithNormalCommand()
    {
        $command = new class() {
        };
        $next = $this->createNext();

        $this->middleware->execute($command, $next);

        // The next callable should be called with the command
        $this->assertSame($command, $next->command);
    }

    private function createNext()
    {
        return new class() {
            public $command;

            public function __invoke($command)
            {
                $this->command = $command;
            }
        };
    }
}
