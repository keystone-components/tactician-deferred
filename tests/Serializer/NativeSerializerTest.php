<?php

namespace Keystone\Tactician\Deferred\Serializer;

class NativeSerializerTest extends \PHPUnit_Framework_TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new NativeSerializer();
    }

    public function testSerialize()
    {
        $command = (object) ['id' => 1];
        $this->assertSame('O:8:"stdClass":1:{s:2:"id";i:1;}', $this->serializer->serialize($command));
    }

    public function testUnserialize()
    {
        $serialized = 'O:8:"stdClass":1:{s:2:"id";i:1;}';
        $command = $this->serializer->unserialize($serialized);
        $this->assertSame(1, $command->id);
    }
}
