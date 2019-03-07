<?php
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
        return $this->subject->header($key, $value, $ucwords);
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
        return $this->subject->cookie($name, $value, $expires, $path, $domain, $secure, $httponly);
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
        return $this->subject->cookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * @param int $code
     * @return mixed
     */
    public function status($code)
    {
        return $this->subject->status($code);
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
