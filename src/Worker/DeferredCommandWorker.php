<?php

namespace Keystone\Tactician\Deferred\Worker;

use Exception;
use Keystone\Tactician\Deferred\DeferredCommand;
use Keystone\Tactician\Deferred\Serializer\SerializerInterface;
use League\Tactician\CommandBus;
use Radish\Broker\Message;

class DeferredCommandWorker
{
    private $commandBus;
    private $serializer;

    public function __construct(CommandBus $commandBus, SerializerInterface $serializer)
    {
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
    }

    public function __invoke(Message $message)
    {
        $command = $this->serializer->unserialize($message->getBody());
        if (!$command) {
            throw new Exception('Failed to unserialize command');
        }

        $this->commandBus->handle(new DeferredCommand($command));
    }
}
