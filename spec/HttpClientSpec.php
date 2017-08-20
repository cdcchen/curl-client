<?php

namespace spec\cdcchen\net\curl;

use cdcchen\net\curl\HttpClient;
use cdcchen\net\curl\HttpRequest;
use cdcchen\net\curl\HttpResponse;
use cdcchen\net\curl\Formatter;
use cdcchen\psr7\Request;
use PhpSpec\ObjectBehavior;

class HttpClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HttpClient::class);
    }

    function i2t_is_request_get_psr7_request()
    {
        $this->beConstructedWith();
        $url = 'http://www.baidu.com';
        $request = new Request('GET', $url);

        $this->request($request)->shouldBeAnInstanceOf(HttpResponse::class);
    }

    function i2t_is_request_post_psr7_request()
    {
        $this->beConstructedWith();
        $url = 'http://127.0.0.1:9898/src/test.php';
        $request = new Request('post', $url);

        $this->request($request)->shouldBeAnInstanceOf(HttpResponse::class);
    }

    function i2t_is_static_request_post_psr7_request()
    {
        $this->beConstructedWith();
        $url = 'http://127.0.0.1:9898/src/test.php?action=user';
        $request = new Request('post', $url, null, 'useranem=cdcchen');

        $this->request($request)->shouldBeAnInstanceOf(HttpResponse::class);
    }

    function it_is_static_request_post_psr7_request()
    {
        $this->beConstructedWith();
        $url = 'http://www.test.com/test.php?action=user';
        $request = new HttpRequest('post', $url);

        $response = $this->setDebug()
                         ->setData(['sex' => 'male'], Formatter::FORMAT_RAW_URLENCODED)
                         ->request($request)
                         ->shouldBeAnInstanceOf(HttpResponse::class);

//        echo $response->getBody();
    }
}
