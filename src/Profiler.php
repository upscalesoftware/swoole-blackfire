<?php
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
     * Start profiling a given request
     *
     * @param \Swoole\Http\Request $request
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
        }
    }

    /**
     * Stop profiling a given request and send results in a response
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function stop(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        if ($this->probe && $this->probe->isEnabled() && $this->request === $request) {
            $this->probe->close();
            list($probeHeaderName, $probeHeaderValue) = explode(':', $this->probe->getResponseLine(), 2);
            $this->reset();
            $response->header(strtolower("x-$probeHeaderName"), trim($probeHeaderValue));
        }
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