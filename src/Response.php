<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/4/10
 * Time: 03:14
 */

namespace cdcchen\net\curl;


class Response
{
    /**
     * @var string|null raw content
     */
    private $_content;

    private $_headers;

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }
}