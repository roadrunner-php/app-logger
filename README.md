<p align="center">
    <a href="https://roadrunner.dev"><picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
        <img alt="RoadRunner" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8" style="width: 6in; display: block">
    </picture></a>
</p>

<p align="center">Send application log messages to RoadRunner</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://docs.roadrunner.dev/docs/logging-and-observability/applogger)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/roadrunner-server)

[![Psalm Level](https://shepherd.dev/github/roadrunner-php/app-logger/level.svg)](https://shepherd.dev/github/roadrunner-php/app-logger)
[![Type Coverage](https://shepherd.dev/github/roadrunner-php/app-logger/coverage.svg)](https://shepherd.dev/github/roadrunner-php/app-logger)

</div>

<br />

A PHP client for the RoadRunner [app-logger plugin](https://docs.roadrunner.dev/docs/logging-and-observability/applogger): it sends log messages from your PHP workers to RoadRunner over RPC, so they end up in the server's own logs.

## Get Started

### Installation

```bash
composer require roadrunner-php/app-logger
```

[![PHP](https://img.shields.io/packagist/php-v/roadrunner-php/app-logger.svg?style=flat-square&logo=php)](https://packagist.org/packages/roadrunner-php/app-logger)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/roadrunner-php/app-logger.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/roadrunner-php/app-logger)
[![License](https://img.shields.io/packagist/l/roadrunner-php/app-logger.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/roadrunner-php/app-logger.svg?style=flat-square)](https://packagist.org/packages/roadrunner-php/app-logger/stats)

### Configuration

Such a configuration would be quite feasible to run:

```yaml
rpc:
  listen: tcp://127.0.0.1:6001

logs:
  channels:
    app:
      level: info
```

### Usage

Create an instance of `RoadRunner\Logger\Logger`:

```PHP
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use RoadRunner\Logger\Logger;

$rpc = RPC::create('tcp://127.0.0.1:6001');
// or, inside a RoadRunner worker (requires spiral/roadrunner-worker)
$rpc = RPC::create(Environment::fromGlobals()->getRPCAddress());

$logger = new Logger($rpc);

$logger->info('Info message');
```

## Available methods

`debug`, `error`, `info` and `warning` are mapped to the RoadRunner logger, and `log` is mapped to stderr.

```PHP
/**
 * debug mapped to RR's debug logger
 */
$logger->debug('Debug message');

/**
 * error mapped to RR's error logger
 */
$logger->error('Error message');

/**
 * log mapped to RR's stderr
 */
$logger->log("Log message \n");

/**
 * info mapped to RR's info logger
 */
$logger->info('Info message');

/**
 * warning mapped to RR's warning logger
 */
$logger->warning('Warning message');
```

## Context

Every method also accepts a context array as the second argument. Its values are sent as log attributes: strings and `Stringable` objects as is, everything else JSON-encoded.

```PHP
$logger->info('User logged in', ['user_id' => 42, 'roles' => ['admin']]);
```

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>
