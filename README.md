Blackfire Profiler for Swoole
=============================

This library enables profiling of PHP applications running on [Swoole](https://www.swoole.co.uk/) web-server via [Blackfire](https://blackfire.io/).

**Features:**
- Custom start/stop profiling calls
- Compatible with [Blackfire Companion](https://blackfire.io/docs/integrations/) browser extensions

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dev dependency:
```bash
composer require upscale/swoole-blackfire --dev
```
## Basic Usage

Surround interested block of code with the profiler start/stop calls:
```php
require 'vendor/autoload.php';

$server = new \Swoole\Http\Server('127.0.0.1', 8080);

$profiler = new \Upscale\Swoole\Blackfire\Profiler();

$server->on('request', function ($request, $response) use ($profiler) {
    $profiler->start($request);

    $response->header('Content-Type', 'text/plain');
    $body = 'Hello World';

    $profiler->stop($request, $response);

    $response->end($body);
});

$server->start();
```

**Note:** Profiler must be stopped before sending response body.
Profiling results are being reported in the response headers.

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).