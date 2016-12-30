<?php

namespace Keystone\Tactician\Deferred\Serializer;

interface SerializerInterface
{
    public function serialize($command);

    public function unserialize($serializedCommand);

    public function getContentType();
}
