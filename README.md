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

$profiler = new \Upscale\Swoole\Profiler();

$server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($profiler) {
    // Start profiling at the very beginning of request
    $profiler->start($request);

    // Execute whatever needs to be profiled
    $response->header('Content-Type', 'text/plain');
    $body = 'Hello World';

    // Stop profiling before sending headers (for profiler to append headers)
    $profiler->stop($request, $response);

    // Send response body
    $response->end($body);
});

$server->start();
```

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).