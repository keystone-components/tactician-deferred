# Deferred Commands with Tactician and Keystone Queue

[![Build Status](https://travis-ci.org/keystonephp/tactician-deferred.png)](https://travis-ci.org/keystonephp/tactician-deferred)

A Tactician middleware to allow deferring commands to be handled asynchronously using [Keystone Queue](https://github.com/keystonephp/queue).

Features:

* Unobtrusive API to allow asynchronous handling of commands.
* Publish command messages to any queue.
* Commands can be retried on failure.

## Installation

Install via Composer

```bash
composer require --dev keystone/tactician-deferred
```

## Usage

Create a command class that implemented `DeferrableCommand` to tell the middleware to handle it asynchronously.

```php
use Keystone\Tactician\Deferred\Command\DeferrableCommand;

class SendWelcomeCommand implements DeferrableCommand
{
    public $email;
    public $name;

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function getKey(): string
    {
        // The command key is used to determine which queue to publish to.
        return 'email';
    }
}
```

Create a command handler as normal using your desired inflector method.

```php
class SendWelcomeHandler
{
    public function handle(SendWelcomeCommand $command)
    {
        // Send the welcome
    }
}
```

When the command is handled by the command bus the middleware will publish a message to the queue and halt the handler.
The message will then be received by the consumer and the worker will delegate the command to the command bus. Since the
command will be serialized within the queue it's properties should be simple scalar values.

```php
$commandBus = new CommandBus([new DeferredMiddleware(...), new CommandHandlerMiddleware(...)]);
$commandBus->handle(new SendWelcomeCommand('ben@flowerpots.com', 'Ben'));
```

Consume the messages in a long running process (see [Keystone Queue](https://github.com/keystonephp/queue) for more information).

```php
use Keystone\Queue\Consumer;
use Keystone\Queue\Provider;

$provider = new Provider(...);
$consumer = new Consumer($provider, ...);
// The consumer will poll the queue for new messages and process them.
$consumer->consume();
```

When the middleware is not configured the command will be handled synchronously as normal, which can be useful for testing
without worrying about asynchronous processes in development environments.

## Credits

- [Tom Graham](https://github.com/tompedals) (maintainer)

## License

Released under the MIT Licence. See the bundled LICENSE file for details.
