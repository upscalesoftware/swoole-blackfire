<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Blackfire;

class Profiler
{
    private ?\BlackfireProbe $probe = null;

    private ?\Swoole\Http\Request $request = null;

    /**
     * Install profiler instrumentation
     */
    public function instrument(\Swoole\Http\Server $server): void
    {
        $server = new \Upscale\Swoole\Reflection\Http\Server($server);
        $server->setMiddleware($this->wrap($server->getMiddleware()));
    }

    /**
     * Invoke a given middleware decorated for profiling
     */
    public function inspect(\Swoole\Http\Request $request, \Swoole\Http\Response $response, callable $middleware): void
    {
        $middleware = $this->wrap($middleware);
        $middleware($request, $response);
    }

    /**
     * Decorate a given middleware for profiling
     */
    private function wrap(callable $middleware): callable
    {
        return new ProfilerDecorator($middleware, $this);
    }

    /**
     * Start profiling a given request
     */
    public function start(\Swoole\Http\Request $request): bool
    {
        if (!$this->probe && isset($request->header['x-blackfire-query'])) {
            $this->probe = new \BlackfireProbe($request->header['x-blackfire-query']);
            $this->request = $request;
            if (!$this->probe->enable()) {
                $this->reset();
                throw new \UnexpectedValueException('Cannot enable Blackfire profiler');
            }
            return true;
        }
        return false;
    }

    /**
     * Stop profiling a given request and send results in a response
     */
    public function stop(\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool
    {
        if ($this->probe && $this->probe->isEnabled() && $this->request === $request) {
            $this->probe->close();
            list($probeHeaderName, $probeHeaderValue) = explode(':', $this->probe->getResponseLine(), 2);
            $this->reset();
            $response->header(strtolower("x-$probeHeaderName"), trim($probeHeaderValue));
            return true;
        }
        return false;
    }

    /**
     * Reset profiling session
     */
    private function reset(): void
    {
        if ($this->probe && $this->probe->isEnabled()) {
            $this->probe->close();
        }
        $this->probe = null;
        $this->request = null;
    }
}