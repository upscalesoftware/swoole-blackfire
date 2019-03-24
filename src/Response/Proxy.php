<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Blackfire\Response;

class Proxy extends \Swoole\Http\Response
{
    /**
     * @var \Swoole\Http\Response 
     */
    protected $subject;
    
    /**
     * Inject dependencies
     * 
     * @param \Swoole\Http\Response $subject
     */
    public function __construct(\Swoole\Http\Response $subject)
    {
        $this->subject = $subject;
        $this->fd = $subject->fd;
    }

    /**
     * @param string $content
     * @return mixed
     */
    public function end($content = '')
    {
        return $this->subject->end($content);
    }

    /**
     * @param string $content
     * @return mixed
     */
    public function write($content)
    {
        return $this->subject->write($content);
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $ucwords
     * @return mixed
     */
    public function header($key, $value, $ucwords = null)
    {
        $result = $this->subject->header($key, $value, $ucwords);
        $this->header = $this->subject->header;
        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function cookie(
        $name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null
    ) {
        $result = $this->subject->cookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function rawcookie(
        $name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null
    ) {
        $result = $this->subject->rawcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param int $code
     * @param string|null $reason
     * @return mixed
     */
    public function status($code, $reason = null)
    {
        return $this->subject->status($code, $reason);
    }

    /**
     * @param int $level
     * @return mixed
     */
    public function gzip($level = 1)
    {
        return $this->subject->gzip($level);
    }

    /**
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @return mixed
     */
    public function sendfile($filename, $offset = null, $length = null)
    {
        return $this->subject->sendfile($filename, $offset, $length);
    }
}
