<?php
namespace Upscale\Swoole\Blackfire;

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
        $observedResponse = new Response\Observable($response);
        $observedResponse->onHeadersSentBefore(function () use ($request, $response) {
            $this->profiler->stop($request, $response);
        });
        if ($this->profiler->start($request)) {
            $middleware($request, $observedResponse);
        } else {
            $middleware($request, $response);
        }
    }
}