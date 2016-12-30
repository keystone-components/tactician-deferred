<?php

namespace Keystone\Tactician\Deferred\Serializer;

class NativeSerializer implements SerializerInterface
{
    public function serialize($command)
    {
        return serialize($command);
    }

    public function unserialize($serializedCommand)
    {
        return unserialize($serializedCommand);
    }

    public function getContentType()
    {
        return 'text/plain';
    }
}
