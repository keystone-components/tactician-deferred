<?php

namespace Keystone\Tactician\Deferred\Worker;

use Keystone\Tactician\Deferred\Command\DeferrableInterface;
use Keystone\Tactician\Deferred\DeferredCommand;
use Keystone\Tactician\Deferred\Serializer\SerializerInterface;
use Keystone\Tactician\Deferred\Testing\MockCommandBus;
use Mockery;
use Radish\Broker\Message;

class DeferredCommandWorkerTest extends \PHPUnit_Framework_TestCase
{
    private $commandBus;
    private $serializer;
    private $worker;

    public function setUp()
    {
        $this->commandBus = new MockCommandBus();
        $this->serializer = Mockery::mock(SerializerInterface::class);
        $this->worker = new DeferredCommandWorker($this->commandBus, $this->serializer);
    }

    public function testHandlesUnserializedCommand()
    {
        $command = Mockery::mock(DeferrableInterface::class);
        $this->serializer->shouldReceive('unserialize')
            ->with('{"id": 1}')
            ->andReturn($command)
            ->once();

        $message = Mockery::mock(Message::class, [
            'getBody' => '{"id": 1}',
        ]);
        $this->worker->__invoke($message);

        $deferredCommand = $this->commandBus->handled[0];
        $this->assertInstanceOf(DeferredCommand::class, $deferredCommand);
        $this->assertSame($command, $deferredCommand->getCommand());
    }

    /**
     * @expectedException \Exception
     */
    public function testThrowsExceptionWhenCommandCannotBeUnserialized()
    {
        $this->serializer->shouldReceive('unserialize')
            ->andReturn(false)
            ->once();

        $message = Mockery::mock(Message::class, [
            'getBody' => '{"id": 1}',
        ]);
        $this->worker->__invoke($message);
    }
}
