<?php

namespace Keystone\Tactician\Deferred;

use Keystone\Tactician\Deferred\Command\DeferrableInterface;
use Keystone\Tactician\Deferred\Command\RetryableInterface;
use Keystone\Tactician\Deferred\Serializer\SerializerInterface;
use Keystone\Mockery\CallableMock;
use League\Tactician\Middleware;
use Mockery;
use Radish\Broker\Message;
use Radish\Producer\ProducerInterface;

class DeferredMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    private $serializer;
    private $producer;
    private $middleware;

    public $nextSpy;

    public function setUp()
    {
        $this->serializer = Mockery::mock(SerializerInterface::class, [
            'serialize' => null,
            'getContentType' => null,
        ]);

        $this->producer = Mockery::mock(ProducerInterface::class, [
            'publish' => null,
        ]);

        $this->middleware = new DeferredMiddleware($this->serializer, $this->producer, 'routing_key');
    }

    public function testContinuesForNonDeferrableCommand()
    {
        $command = (object) [];
        $next = new CallableMock();
        $next->shouldBeCalled()->with($command)->once();

        $this->middleware->execute($command, $next);
    }

    public function testPublishesTaskMessageWhenCommandIsDeferrable()
    {
        $command = new TestCommand();
        $next = new CallableMock();

        $this->serializer->shouldReceive('serialize')
            ->with($command)
            ->andReturn('[]');

        $this->serializer->shouldReceive('getContentType')
            ->andReturn('application/json');

        $this->producer->shouldReceive('publish')
            ->with(Mockery::on(function (Message $message) {
                $this->assertSame('routing_key', $message->getRoutingKey());
                $this->assertSame('[]', $message->getBody());
                $this->assertSame('application/json', $message->getContentType());

                return true;
            }))
            ->once();

        $this->middleware->execute($command, $next);
    }

    public function testPublishesTaskMessageAsRetryableWhenCommandIsRetryable()
    {
        $command = new RetryableTestCommand();
        $next = new CallableMock();

        $this->serializer->shouldReceive('serialize')
            ->with($command)
            ->andReturn('[]');

        $this->serializer->shouldReceive('getContentType')
            ->andReturn('application/json');

        $this->producer->shouldReceive('publish')
            ->with(Mockery::on(function (Message $message) {
                $this->assertSame(['max_attempts' => 10], $message->getHeader('retry_options'));

                return true;
            }))
            ->once();

        $this->middleware->execute($command, $next);
    }

    public function testDoesNotContinueWhenCommandIsDeferrable()
    {
        $command = new TestCommand();
        $next = new CallableMock();
        $next->shouldBeCalled()->never();

        $this->middleware->execute($command, $next);
    }

    public function testUsesInnerCommandForDeferrableCommand()
    {
        $innerCommand = new TestCommand();
        $command = new DeferredCommand($innerCommand);

        $next = new CallableMock();
        $next->shouldBeCalled()->with($innerCommand)->once();

        $this->middleware->execute($command, $next);
    }
}

class TestCommand implements DeferrableInterface
{
}

class RetryableTestCommand implements DeferrableInterface, RetryableInterface
{
    public function getMaxRetries()
    {
        return 10;
    }
}
