<?php

namespace Keystone\Tactician\Deferred;

use Keystone\Tactician\Deferred\Command\DeferrableInterface;
use Keystone\Tactician\Deferred\Command\RetryableInterface;
use Keystone\Tactician\Deferred\Serializer\SerializerInterface;
use League\Tactician\Middleware;
use Radish\Broker\Message;
use Radish\Producer\ProducerInterface;

class DeferredMiddleware implements Middleware
{
    private $serializer;
    private $producer;
    private $routingKey;

    public function __construct(SerializerInterface $serializer, ProducerInterface $producer, $routingKey)
    {
        $this->serializer = $serializer;
        $this->producer = $producer;
        $this->routingKey = $routingKey;
    }

    public function execute($command, callable $next)
    {
        if ($command instanceof DeferrableInterface) {
            // The command should be deferred and pushed to a worker
            $this->producer->publish($this->createMessage($command));

            return;
        }

        if ($command instanceof DeferredCommand) {
            // The inner command is the original deferred command
            $command = $command->getCommand();
        }

        return $next($command);
    }

    private function createMessage($command)
    {
        $message = new Message();
        $message->setDeliveryMode(AMQP_DURABLE);
        $message->setRoutingKey($this->routingKey);
        $message->setBody($this->serializer->serialize($command));
        $message->setContentType($this->serializer->getContentType());

        if ($command instanceof RetryableInterface) {
            $message->setHeader('retry_options', [
                'max_attempts' => $command->getMaxRetries(),
            ]);
        }

        return $message;
    }
}
