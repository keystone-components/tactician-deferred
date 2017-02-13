<?php declare(strict_types=1);

namespace Keystone\Tactician\Deferred\Command;

use Keystone\Queue\Message;

/**
 * A command which can be deferred for handling within a background task.
 */
interface DeferrableCommand extends Message
{
}
