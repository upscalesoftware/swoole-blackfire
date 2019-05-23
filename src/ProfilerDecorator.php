<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Blackfire;

use Upscale\Swoole\Reflection\Http\Response;

class ProfilerDecorator
{
    /**
     * @var callable
     */
    protected $subject;

    /**
     * @var Profiler
     */
    protected $profiler;

    /**
     * Inject dependencies
     * 
     * @param callable $subject
     * @param Profiler $profiler
     */
    public function __construct(callable $subject, Profiler $profiler)
    {
        $this->subject = $subject;
        $this->profiler = $profiler;
    }

    /**
     * Invoke the underlying middleware surrounding it with the profiler start/stop calls  
     * 
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function __invoke(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $middleware = $this->subject;
        if ($this->profiler->start($request)) {
            try {
                $observedResponse = new Response\Observable($response);
                $observedResponse->onHeadersSentBefore(function () use ($request, $response) {
                    $this->profiler->stop($request, $response);
                });
                $middleware($request, $observedResponse);
            } finally {
                $this->profiler->stop($request, $response);
            }
        } else {
            $middleware($request, $response);
        }
    }
}