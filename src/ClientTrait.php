<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/4
 * Time: 下午5:58
 */

namespace cdcchen\net\curl;


/**
 * Class Client
 * @package cdcchen\net\curl
 */

use cdcchen\psr7\Request;
use cdcchen\psr7\StreamHelper;
use cdcchen\psr7\Uri;
use Psr\Http\Message\StreamInterface;

/**
 * Trait Client
 * @package cdcchen\net\curl
 */
trait ClientTrait
{
    /**
     * Http GET method request shortcut
     *
     * @param string $url
     * @param null|array|string $queryParams
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function get(string $url, $queryParams = null, array $headers = [], array $options = []): HttpResponse
    {
        $uri = new Uri($url);

        if (is_array($queryParams)) {
            $queryString = http_build_query($queryParams);
        } elseif (is_string($queryParams)) {
            $queryString = $queryParams;
        } else {
            throw new \InvalidArgumentException('queryParams must be string or array.');
        }

        if ($queryString) {
            $uri->withQuery($queryString);
        }

        return static::request('get', $uri, null, $headers, $options);
    }

    /**
     * Http POST method request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function post(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('post', $url, $data, $headers, $options);
    }

    /**
     * Http PUT method request shortcut
     *
     * @param string $url
     * @param null|array $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function put(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('put', $url, $data, $headers, $options);
    }

    /**
     * Http GET method request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function head(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('head', $url, $data, $headers, $options);
    }

    /**
     * Http PATCH method request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function patch(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('patch', $url, $data, $headers, $options);
    }

    /**
     * Http OPTIONS method request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function options(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('options', $url, $data, $headers, $options);
    }

    /**
     * Http DELETE method request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    public static function delete(string $url, $data = null, array $headers = [], array $options = []): HttpResponse
    {
        return static::request('delete', $url, $data, $headers, $options);
    }

    /**
     * Http upload request shortcut
     *
     * @param string $url
     * @param null|array|string $data
     * @param array $files
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     * @todo not completed
     */
    public static function upload(
        string $url,
        array $files = [],
        $data = null,
        array $headers = [],
        array $options = []
    ): HttpResponse {
        return static::request('post', $url, $data, $headers, $options);
    }

    /**
     * @param string $method
     * @param string $url
     * @param null|string $body
     * @param array $headers
     * @param array $options
     * @return HttpResponse
     */
    private static function request(
        string $method,
        string $url,
        string $body = null,
        array $headers,
        array $options
    ): HttpResponse {
        $request = new Request($method, $url, $headers);
        $client = (new HttpClient())->addOptions($options);

        if ($body !== null) {
            if (!($body instanceof StreamInterface)) {
                $body = StreamHelper::createStream($body);
            }
            $request = $request->withBody($body);
        }

        return $client->request($request);
    }
}