# Core Logger, stand-alone.

MCP-Logger is a module that lets an application log entries to our internal [Core Logger](http://core) service.

Using this package, an application can write warnings, fatals, and stack traces to Core.

Table of Contents:
* [Installation](#installation)
* [Components](#components)
* [Using MCP Logger](#using-mcp-logger)
* [PSR-3](#psr-3)
* [Components In Detail](#components-in-detail)
* [Contribute](#contribute)

See Also:

* [Core Logger](http://core)
* [Core Logger Specifications](https://itiki/index.php/Core_Logger)

## Installation

Installation with composer is recommended. Other methods are unsupported.

Add the following to your project's `composer.json`:

```javascript
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://composer/"
        }
    ],
    "require": {
        "ql/mcp-logger": "*"
    }
}
```

## Components

The MCP Logger consists of 3 main components:

* [MCP\Service\Logger\MessageInterface](#mcpserviceloggermessageinterface)
* [MCP\Service\Logger\RendererInterface](#mcpserviceloggerrendererinterface)
* [MCP\Service\Logger\ServiceInterface](#mcpserviceloggerserviceinterface)

**In a single sentence:**  
The `Renderer` renders a `Message` that is sent by the `Service`.

There are default implementations of each of these components:

* [MCP\Service\Logger\Message\Message](#mcpserviceloggermessageinterface)
* [MCP\Service\Logger\Renderer\XmlRenderer](#mcpserviceloggerrendererinterface)
* [MCP\Service\Logger\Service\HttpService](#mcpserviceloggerserviceinterface)

In addition there are several convenience classes:

A PSR-3 Logger:

* [MCP\Service\Logger\Logger](#psr-3)

A Message Factory

* [MCP\Service\Logger\Message\MessageFactory](#build-a-message-with-the-messagefactory)

## Using MCP Logger

#### Setup

**Note:**  
The target url **MUST** be set on the `Client` or `Request` that is passed to the service.

```php
use Guzzle\Http\Client;
use MCP\Service\Logger\Renderer\XmlRenderer;
use MCP\Service\Logger\Service\GuzzleService;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$client = new Client('http://sonic');
$service = new GuzzleService($client, $renderer);
```

#### Sending a message

There are 5 required fields to create a message. By default, the standard message level is `INFO`. To send a message
at a different level, you must provide it in the message data.

```php
use MCP\DataType\IPv4Address;
use MCP\DataType\Time\TimePoint;
use MCP\Service\Logger\Message\Message;

$message = new Message(
    array(
        'applicationId' => '1',
        'createTime' => new TimePoint(2013, 8, 15, 0, 0, 0, 'UTC'),
        'machineIPAddress' => new IPv4Address(0),
        'machineName' => 'ServerName',
        'message' => 'This is a message'
    )
);

// Send a message
$service->send($message);
```

#### Build a message with the MessageFactory

Alternatively, a convenience factory is provided that will allow you to pass message defaults at setup
so you do not have to populate these fields every time a message is logged.

The factory will add `createTime`, `message`, and `level` to the message payload.
```php
use MCP\DataType\Time\Clock;
use MCP\Service\Logger\Message\MessageFactory;

$clock = new Clock('now', 'UTC');
$factory = new MessageFactory($clock);

$message = $factory->buildMessage(MessageFactory::DEBUG, 'A debug message');
```

There are three ways to add data to a message when using the factory.

In the constructor:
```php
$factory = new MessageFactory($clock, array(
    'applicationId' => '1',
    'machineIPAddress' => new MCP\DataType\IPv4Address(0)
));
```

With a setter
```php
$factory->setDefaultProperty('machineName', 'ServerName');
```

As context data when building the message:
```php
$message = $factory->buildMessage(
    MessageFactory::DEBUG,
    'A debug message',
    array('userIPAddress' => new MCP\DataType\IPv4Address(0))
);
```

Unknown fields that the core service does not understand will be automatically added to `Extended Properties`
by the factory.

## PSR-3

If your application does not require a complex logging setup (e.g., cascading loggers), and is compatible
with PSR-3, a PSR-3 Logger is provided.

This logger has the Service and MessageFactory as dependencies. The logger uses a different MessageFactory that
specifically converts a PSR-3 log level to a core log level.

**Note**: You must still provide the required message properties to the factory.

### In use

```php
use Guzzle\Http\Client;
use MCP\Service\Logger\Adapter\Psr\MessageFactory;
use MCP\Service\Logger\Logger;
use MCP\Service\Logger\Renderer\XmlRenderer;
use MCP\Service\Logger\Service\GuzzleService;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$client = new Client('http://sonic');
$service = new GuzzleService($client, $renderer);

$clock = new Clock('now', 'UTC');
$factory = new MessageFactory($clock);
$logger = new Logger($service, $factory);

// Do not forget to add the required properties!
$factory->setDefaultProperty('applicationId', 1);
$factory->setDefaultProperty('machineIPAddress', new IPv4Address(0));
$factory->setDefaultProperty('machineName', 'ServerName');

// Log an error
$logger->error('Error Message!');

// Log a warning
$context = array('exceptionData' => 'stacktrace dump here');
$logger->warning('Warning Message!', $context);
```

## Components In Detail

#### MCP\Service\Logger\MessageInterface

The `Message` object has the following properties:

```php
$message->affectedSystem();
$message->applicationId();
$message->categoryId();
$message->createTime();
$message->exceptionData();
$message->extendedProperties();
$message->isUserDisrupted();
$message->level();
$message->loanNumber();
$message->machineIPAddress();
$message->machineName();
$message->message();
$message->referrer();
$message->requestMethod();
$message->url();
$message->userAgentBrowser();
$message->userCommonId();
$message->userDisplayName();
$message->userIPAddress();
$message->userName();
$message->userScreenName();
```
Each of these may be set by adding a `key => value` pair to the data array when constructing a message.

The following properties are required:

* applicationId
* createTime
* machineIPAddress
* machineName
* message

The following properties are required but will populate with defaults if missing:

* extendedProperties
* level
* isUserDisrupted

See also:

* [MessageInterface.php](src/Logger/MessageInterface.php)
* [Message.php](src/Logger/Message/Message.php)
* [Core Logger Specifications](https://itiki/index.php/Core_Logger)

#### MCP\Service\Logger\RendererInterface

The `Renderer` is not directly used by consumers of this package. The renderer provided to the
service will be invoked upon the message and format the message so it can be sent. The provided renderer
converts a message to an XML string.

```php
use MCP\Service\Logger\Renderer\XmlRenderer;
use XMLWriter;

$renderer = new XmlRenderer(new XMLWriter);
$output = $renderer($message);
```

See also:

* [RendererInterface.php](src/Logger/RendererInterface.php)
* [XmlRenderer.php](src/Logger/Renderer/XmlRenderer.php)

#### MCP\Service\Logger\ServiceInterface

By default, the provided Http Service silently consumes exceptions if the http request fails.

```php
use MCP\Service\Logger\Service\HttpService;

$isSilent = false;
$service = new HttpService($request, $renderer, $isSilent);
$service->send($message);
```

See also:

* [ServiceInterface.php](src/Logger/ServiceInterface.php)
* [HttpService.php](src/Logger/Service/HttpService.php)
* [GuzzleService.php](src/Logger/Service/GuzzleService.php)

## Contribute

#### Standards

This library follows PSR-2 conventions.

#### Install development dependencies

```bash
bin/install
```

#### Wipe compiled and built files

```bash
bin/clean
```

#### Run unit tests

```bash
vendor/bin/phpunit

# Run specific unit tests
vendor/bin/phpunit <file|directory>

# Run integration tests
vendor/bin/phpunit --group integration
```
