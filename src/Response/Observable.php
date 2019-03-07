<?php
namespace Upscale\Swoole\Blackfire\Response;

class Observable extends Proxy
{
    /**
     * @var bool
     */
    protected $isHeadersSent = false;
    
    /**
     * @var callable[] 
     */
    protected $headersSentObservers = [];

    /**
     * {@inheritdoc}
     */
    public function end($content = '')
    {
        $this->doHeadersSentBefore();
        return parent::end($content);
    }

    /**
     * {@inheritdoc}
     */
    public function write($content)
    {
        $this->doHeadersSentBefore();
        return parent::write($content);
    }

    /**
     * {@inheritdoc}
     */
    public function sendfile($filename, $offset = null, $length = null)
    {
        $this->doHeadersSentBefore();
        return parent::sendfile($filename, $offset, $length);
    }

    /**
     * Subscribe a callback to be notified before sending headers
     * 
     * @param callable $callback
     */
    public function onHeadersSentBefore(callable $callback)
    {
        $this->headersSentObservers[] = $callback;
    }

    /**
     * Notify registered observers
     */
    protected function doHeadersSentBefore()
    {
        if (!$this->isHeadersSent) {
            $this->isHeadersSent = true;
            foreach ($this->headersSentObservers as $callback) {
                $callback();
            }
        }
    }
}
