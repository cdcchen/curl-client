<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/4/10
 * Time: 18:00
 */

namespace cdcchen\net\curl;

use cdcchen\psr7\Response;


/**
 * Class JsonParser
 * @package cdcchen\net\curl
 */
class JsonParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(Response $response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}