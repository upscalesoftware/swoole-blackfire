<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Blackfire;

use Upscale\Swoole\Reflection\Http\Response;

class ProfilerDecorator
{
    /** @var callable */
    protected $subject;

    protected Profiler $profiler;

    /**
     * Inject dependencies
     */
    public function __construct(callable $subject, Profiler $profiler)
    {
        $this->subject = $subject;
        $this->profiler = $profiler;
    }

    /**
     * Invoke the underlying middleware surrounding it with the profiler start/stop calls  
     */
    public function __invoke(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
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