<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Blackfire\Tests;

use Upscale\Swoole\Blackfire\Profiler;
use Upscale\Swoole\Launchpad\Tests\TestCase;

class ProfilerTest extends TestCase
{
    private \Swoole\Http\Server $server;

    private Profiler $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new \Swoole\Http\Server('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 3,
            'dispatch_mode' => 1,
        ]);

        $this->subject = new Profiler();
    }

    public function testInactive()
    {
        $this->server->on('request', function ($request, $response) {
            $response->header('Content-Type', 'text/plain');
            $response->end(
                'CRC32: ' . hash_file('crc32b', __FILE__) . "\n" .
                'MD5:   ' . md5_file(__FILE__) . "\n" .
                'SHA1:  ' . sha1_file(__FILE__) . "\n"
            );
        });

        $this->spawn($this->server);

        $result = `blackfire curl http://127.0.0.1:8080/ 2>&1`;
        $this->assertStringContainsString('No probe response, Blackfire not properly installed', $result);
        $this->assertStringNotContainsString('Blackfire cURL completed', $result);
    }

    public function testInstrument()
    {
        $this->server->on('request', function ($request, $response) {
            $response->header('Content-Type', 'text/plain');
            $response->end(
                'CRC32: ' . hash_file('crc32b', __FILE__) . "\n" .
                'MD5:   ' . md5_file(__FILE__) . "\n" .
                'SHA1:  ' . sha1_file(__FILE__) . "\n"
            );
        });

        $this->subject->instrument($this->server);

        $this->spawn($this->server);

        $result = `blackfire curl http://127.0.0.1:8080/ 2>&1`;
        $this->assertStringContainsString('Blackfire cURL completed', $result);
    }

    public function testInspect()
    {
        $this->server->on('request', function ($request, $response) {
            $response->header('Content-Type', 'text/plain');

            $this->subject->inspect($request, $response, function ($request, $response) {
                $response->write('CRC32: ' . hash_file('crc32b', __FILE__) . "\n");
            });

            $response->write('MD5:   ' . md5_file(__FILE__) . "\n");
            $response->write('SHA1:  ' . sha1_file(__FILE__) . "\n");
        });

        $this->spawn($this->server);

        $result = `blackfire curl http://127.0.0.1:8080/ 2>&1`;
        $this->assertStringContainsString('Blackfire cURL completed', $result);
    }

    public function testStartStop()
    {
        $this->server->on('request', function ($request, $response) {
            $response->header('Content-Type', 'text/plain');

            $output = 'CRC32: ' . hash_file('crc32b', __FILE__) . "\n";

            $this->subject->start($request);
            $output .= 'MD5:   ' . md5_file(__FILE__) . "\n";
            $this->subject->stop($request, $response);

            $output .= 'SHA1:  ' . sha1_file(__FILE__) . "\n";
    
            $response->end($output);
        });

        $this->spawn($this->server);

        $result = `blackfire curl http://127.0.0.1:8080/ 2>&1`;
        $this->assertStringContainsString('Blackfire cURL completed', $result);
    }
}