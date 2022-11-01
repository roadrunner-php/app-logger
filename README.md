# Package for sending log messages to RoadRunner

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+

## Installation

You can install the package via composer:

```bash
composer require roadrunner-php/app-logger
```

## Usage

Such a configuration would be quite feasible to run:

```yaml
version: '2.7'

rpc:
  listen: tcp://127.0.0.1:6001
```

Then you need to create an instance of `RoadRunner\Logger\Logger`

```PHP
use Spiral\Goridge\RPC\RPC;
use RoadRunner\Logger\Logger;

$rpc = RPC::create('tcp://127.0.0.1:6001');
// or
$rpc = RPC::fromGlobals();
// or
$rpc = RPC::fromEnvironment(new \Spiral\RoadRunner\Environment([
    'RR_RPC' => 'tcp://127.0.0.1:6001'
]));

$logger = new Logger($rpc);
```

## Available methods
```debug```, ```error```, ```info```, ```warning``` is RoadRunner logger, and ```log``` is stderr
```PHP
$logger->debug('Debug message');

$logger->error('Error message');

$logger->log("Log message \n");

$logger->info('Info message');

$logger->warning('Warning message');
```
