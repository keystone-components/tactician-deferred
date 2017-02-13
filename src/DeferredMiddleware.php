<?php declare(strict_types=1);

namespace Keystone\Tactician\Deferred;

use Keystone\Queue\Publisher;
use Keystone\Tactician\Deferred\Command\DeferrableCommand;
use Keystone\Tactician\Deferred\Command\DeferredCommand;
use League\Tactician\Middleware;

/**
 * Command bus middleware that will publish deferrable commands to the queue for processing
 * asynchronously. Wrapped deferred commands will continue handling by the command bus.
 */
class DeferredMiddleware implements Middleware
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof DeferrableCommand) {
            // The command should be deferred and pushed to a worker
            $this->publisher->publish($command);

            return;
        }

        if ($command instanceof DeferredCommand) {
            // The inner command is the original deferred command
            $command = $command->getCommand();
        }

        return $next($command);
    }
}
