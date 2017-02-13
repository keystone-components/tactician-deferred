<?php declare(strict_types=1);

namespace Keystone\Tactician\Deferred\Command;

/**
 * Wraps the deferrable command so that it can be handled by the middleware.
 */
class DeferredCommand
{
    /**
     * @var DeferrableCommand
     */
    private $command;

    /**
     * @param DeferrableCommand $command
     */
    public function __construct(DeferrableCommand $command)
    {
        $this->command = $command;
    }

    /**
     * @return DeferrableCommand
     */
    public function getCommand(): DeferrableCommand
    {
        return $this->command;
    }
}
