<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/4/10
 * Time: 18:00
 */

namespace cdcchen\curl;


class JsonParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(Response $response)
    {
        return json_decode($response->getContent(), true);
    }
}