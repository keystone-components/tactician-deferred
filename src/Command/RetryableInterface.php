<?php

namespace Keystone\Tactician\Deferred\Command;

interface RetryableInterface
{
    public function getMaxRetries();
}
