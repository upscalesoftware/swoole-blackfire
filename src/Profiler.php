<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Blackfire;

class Profiler
{
    /**
     * @var \BlackfireProbe
     */
    protected $probe;

    /**
     * @var \Swoole\Http\Request
     */
    protected $request;

    /**
     * Install profiler instrumentation
     *
     * @param \Swoole\Http\Server $server
     * @throws \UnexpectedValueException
     */
    public function instrument(\Swoole\Http\Server $server)
    {
        $middleware = $server->onRequest;
        if (!is_callable($middleware)) {
            throw new \UnexpectedValueException('Server middleware has not been initialized yet.');
        }
        $server->on('request', $this->wrap($middleware));
    }

    /**
     * Invoke a given middleware decorated for profiling
     * 
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @param callable $middleware
     */
    public function inspect(\Swoole\Http\Request $request, \Swoole\Http\Response $response, callable $middleware)
    {
        $middleware = $this->wrap($middleware);
        $middleware($request, $response);
    }

    /**
     * Decorate a given middleware for profiling
     * 
     * @param callable $middleware
     * @return ProfilerDecorator
     */
    public function wrap(callable $middleware)
    {
        return new ProfilerDecorator($middleware, $this);
    }

    /**
     * Start profiling a given request
     *
     * @param \Swoole\Http\Request $request
     * @return bool
     */
    public function start(\Swoole\Http\Request $request)
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
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return bool
     */
    public function stop(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
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
    public function reset()
    {
        if ($this->probe && $this->probe->isEnabled()) {
            $this->probe->close();
        }
        $this->probe = null;
        $this->request = null;
    }
}