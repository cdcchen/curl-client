<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/4
 * Time: 下午5:58
 */

namespace cdcchen\curl;


class Client
{
    public static function get($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('get', $url, $data, $headers, $options);
    }

    public static function post($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('post', $url, $data, $headers, $options);
    }

    public static function put($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('put', $url, $data, $headers, $options);
    }

    public static function head($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('head', $url, $data, $headers, $options);
    }

    public static function patch($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('patch', $url, $data, $headers, $options);
    }

    public static function options($url, $data = null, $headers = [], $options = [])
    {
        return static::createHttpRequestShortcut('options', $url, $data, $headers, $options);
    }

    public static function upload($url, $files = [], $data = null, $headers = [], $options = [])
    {
        $request = static::createHttpRequestShortcut('post', $url, $data, $headers, $options);
        foreach ($files as $name => $file) {
            $request->addFile($name, $file);
        }

        return $request;
    }

    private static function createHttpRequestShortcut($method, $url, $data, $headers, $options)
    {
        /* @var HttpRequest $request */
        $request = (new HttpRequest())
            ->setMethod($method)
            ->addHeaders($headers)
            ->setUrl($url)
            ->addOptions($options);

        if (is_array($data)) {
            $request->setData($data);
        } else {
            $request->setContent($data);
        }

        return $request;
    }
}