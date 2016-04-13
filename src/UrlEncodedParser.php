<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/4/10
 * Time: 18:44
 */

namespace cdcchen\curl;


class UrlEncodedParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(Response $response)
    {
        $data = [];
        parse_str($response->getContent(), $data);
        return $data;
    }
}