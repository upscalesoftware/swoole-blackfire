Blackfire Profiler for Swoole
=============================

This library enables profiling of PHP applications running on [Swoole](https://www.swoole.co.uk/) web-server via [Blackfire](https://blackfire.io/).

**Features:**
- Transparent request profiling
- Selective sub-system profiling
- Custom start/stop profiling calls
- [Blackfire Companion](https://blackfire.io/docs/integrations/) integration

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dev dependency:
```bash
composer require upscale/swoole-blackfire --dev
```
## Usage

### Request Profiling

The easiest way to start profiling is to activate the profiler globally for all requests from start to finish.
This approach is by design completely transparent to an application running on the server.
No code changes are needed beyond editing a few lines of code in the server entry point.

Install the profiling instrumentation for all requests:
```php
$server->on('request', function ($request, $response) {
    $response->header('Content-Type', 'text/plain');
    $response->end(
        'CRC32: ' . hash_file('crc32b', __FILE__) . "\n" .
        'MD5:   ' . md5_file(__FILE__) . "\n" .
        'SHA1:  ' . sha1_file(__FILE__) . "\n"
    );
});

$profiler = new \Upscale\Swoole\Blackfire\Profiler();
$profiler->instrument($server);
```

### Selective Profiling

It is possible to limit the profiling scope by wrapping the interested code in a profiler call.

Wrap the code intended to be profiled in the profiler call: 
```php
$profiler = new \Upscale\Swoole\Blackfire\Profiler();

$server->on('request', function ($request, $response) use ($profiler) {
    $response->header('Content-Type', 'text/plain');

    $profiler->inspect($request, $response, function ($request, $response) {
        $response->write('CRC32: ' . hash_file('crc32b', __FILE__) . "\n");    
    });
    
    $response->write('MD5:   ' . md5_file(__FILE__) . "\n");
    $response->write('SHA1:  ' . sha1_file(__FILE__) . "\n");
});
```

Currently, only one profiler inspection call is permitted per request.

### Manual Profiling

Depending on the application design and complexity, it may be difficult to precisely wrap desired code in the profiler call.
Profiler start/stop calls can be manually placed at different call stack levels to further narrow down the inspection scope.
Developer is responsible for the symmetry of the start/stop calls taking into account the response population workflow.
The profiling must be stopped before sending the response body to be able to send the results in the response headers.  

Surround the code intended to be profiled with the profiler start/stop calls:
```php
$profiler = new \Upscale\Swoole\Blackfire\Profiler();

$server->on('request', function ($request, $response) use ($profiler) {
    $response->header('Content-Type', 'text/plain');
    
    $output = 'CRC32: ' . hash_file('crc32b', __FILE__) . "\n";
    
    $profiler->start($request);
    $output .= 'MD5:   ' . md5_file(__FILE__) . "\n";
    $profiler->stop($request, $response);
    
    $output .= 'SHA1:  ' . sha1_file(__FILE__) . "\n"
    
    $response->end($output);
});
```

Currently, only one pair of the profiler start/stop calls is permitted per request.

## Limitations

The profiling implicitly stops before sending the response body and the results are added to the response headers.
Currently, only one profiling session initiated by `inspect()` or `start/stop()` calls is supported per request.

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Upscale Software. All rights reserved.

Licensed under the [Apache License, Version 2.0](https://github.com/upscalesoftware/swoole-blackfire/blob/master/LICENSE.txt).