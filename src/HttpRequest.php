<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/4/10
 * Time: 03:52
 */

namespace cdcchen\net\curl;

use InvalidArgumentException;
use CURLFile;

require_once __DIR__ . '/http_build_url.php';

class HttpRequest extends Request
{
    /**
     * JSON format
     */
    const FORMAT_JSON = 'json';
    /**
     * urlencoded by RFC1738 query string, like name1=value1&name2=value2
     * @see http://php.net/manual/en/function.urlencode.php
     */
    const FORMAT_URLENCODED = 'urlencoded';
    /**
     * urlencoded by PHP_QUERY_RFC3986 query string, like name1=value1&name2=value2
     * @see http://php.net/manual/en/function.rawurlencode.php
     */
    const FORMAT_RAW_URLENCODED = 'raw-urlencoded';
    /**
     * XML format
     */
    const FORMAT_XML = 'xml';

    private $_headers = [];
    private $_cookies = [];
    private $_files   = [];
    private $_content;
    private $_data;
    private $_format  = self::FORMAT_URLENCODED;
    private $_method  = 'get';


    public function prepare()
    {
        $this->setHeaders()
             ->setCookies()
             ->setHttpMethod()
             ->setPostFields();
    }

    private function setHttpMethod()
    {
        $method = strtoupper($this->getMethod());
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $this->addOption(CURLOPT_POST, true);
                break;
            default:
                $this->addOption(CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        return $this;
    }


    private function setPostFields()
    {
        $data = $this->getData();
        if ($this->_files) {
            $data = $data ? array_merge($data, $this->_files) : $this->_files;
            return $this->addOption(CURLOPT_POSTFIELDS, $data);
        }

        if ($data) {
            $this->getFormatter()->format($this);
        }

        $content = $this->getContent();
        if ($content !== null) {
            $this->addOption(CURLOPT_POSTFIELDS, $content)
                 ->addHeader('Content-Length', strlen($content));
        }

        return $this;
    }


    /**
     * @param string $method request method
     * @return $this self reference.
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * @return string request method
     */
    public function getMethod()
    {
        return $this->_method;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    public function getFormat()
    {
        if ($this->_format === null) {
            $this->_format = $this->defaultFormat();
        }

        return $this->_format;
    }

    public function defaultFormat()
    {
        return self::FORMAT_URLENCODED;
    }


    /**
     * Returns HTTP message formatter instance for the specified format.
     * @return FormatterInterface formatter instance.
     * @throws InvalidArgumentException on invalid format name.
     */
    public function getFormatter()
    {
        static $defaultFormatters = [
            self::FORMAT_JSON => 'cdcchen\net\curl\JsonFormatter',
            self::FORMAT_URLENCODED => [
                'class' => 'cdcchen\net\curl\UrlEncodedFormatter',
                'encodingType' => PHP_QUERY_RFC1738
            ],
            self::FORMAT_RAW_URLENCODED => [
                'class' => 'cdcchen\net\curl\UrlEncodedFormatter',
                'encodingType' => PHP_QUERY_RFC3986
            ],
            self::FORMAT_XML => 'cdcchen\net\curl\XmlFormatter',
        ];

        if (!isset($defaultFormatters[$this->getFormat()])) {
            throw new InvalidArgumentException("Unrecognized format '{$this->getFormat()}'");
        }
        $formatter = $defaultFormatters[$this->getFormat()];

        if (!is_object($formatter)) {
            if (is_array($formatter) && isset($formatter['class'])) {
                $className = $formatter['class'];
                $encodingType = $formatter['encodingType'];
                $formatter = new $className;
                $formatter->encodingType = $encodingType;
            } else {
                $formatter = new $formatter();
            }

        }

        return $formatter;
    }

    public function addFiles($input_name, array $files, $mime_type = null, $post_name = null)
    {
        foreach ($files as $index => $file) {
            $inputName = "{$input_name}[{$index}]";
            $this->addFile($inputName, $file, $mime_type, $post_name);
        }

        return $this;
    }

    public function addFile($input_name, $file, $mime_type = null, $post_name = null)
    {
        if ($file instanceof CURLFile) {
            $this->_files[$input_name] = $file;
        } else {
            $this->_files[$input_name] = new CURLFile($file, $mime_type, $post_name);
        }

        return $this;
    }

    public function clearFiles()
    {
        $this->_files = [];
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        return $this;
    }

    public function addHeaders($headers)
    {
        $headers = (array)$headers;
        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                $pos = stripos(trim($value), ':');
                $name = trim(substr($value, 0, $pos));
                $value = trim(substr($value, $pos + 1));
            }
            $this->addHeader($name, $value);
        }

        return $this;
    }

    private function setHeaders()
    {
        $headers = [];
        foreach ($this->_headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return $this->addOption(CURLOPT_HTTPHEADER, $headers);
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setReferrer($referrer = true)
    {

        if (is_bool($referrer)) {
            $this->addOption(CURLOPT_AUTOREFERER, $referrer);
        } elseif (is_string($referrer)) {
            $this->addOptions([CURLOPT_AUTOREFERER => false, CURLOPT_REFERER => $referrer]);
        }

        return $this;
    }

    public function setBasicAuth($username, $password)
    {
        if (!empty($username)) {
            return $this->addOptions([
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => "{$username}:{$password}"
            ]);
        } else {
            throw new InvalidArgumentException('Username is required.');
        }
    }

    public function setUserPassword($username, $password)
    {
        return $this->addOption(CURLOPT_USERPWD, "{$username}:{$password}");
    }


    public function setUserAgent($agent)
    {
        if ($agent) {
            $this->addOption(CURLOPT_USERAGENT, $agent);
        }

        return $this;
    }

    public function setEnableSessionCookie($value = false)
    {
        return $this->addOption(CURLOPT_COOKIESESSION, (bool)$value);
    }


    public function addCookie($name, $value)
    {
        $this->_cookies[$name] = $value;
        return $this;
    }

    public function addCookies($cookies)
    {
        $cookies = (array)$cookies;
        foreach ($cookies as $name => $value) {
            if (is_int($name)) {
                $pos = stripos(trim($value), ':');
                $name = trim(substr($value, 0, $pos));
                $value = trim(substr($value, $pos + 1));
            }
            $this->addCookie($name, $value);
        }

        return $this;
    }

    private function setCookies()
    {
        $cookies = [];
        foreach ($this->_cookies as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }

        return $this->addOption(CURLOPT_COOKIE, $cookies);
    }

    public function setCookieFile($file, $jar = null)
    {
        if ($file && is_writable($file) && is_readable($file)) {
            return $this->addOptions([
                CURLOPT_COOKIEFILE => $file,
                CURLOPT_COOKIEJAR => $jar ?: $file,
            ]);
        } else {
            throw new InvalidArgumentException('Cookie file is required and writable and readable.');
        }
    }

    public function setFollowLocation($value = true, $maxRedirects = 5)
    {
        return $this->addOptions([
            CURLOPT_AUTOREFERER => (bool)$value,
            CURLOPT_FOLLOWLOCATION => (bool)$value,
            CURLOPT_MAXREDIRS => $maxRedirects,
        ]);
    }

    public function setAllowUpload($value = true, $safe = true)
    {
        return $this->addOptions([
            CURLOPT_UPLOAD => (bool)$value,
            CURLOPT_SAFE_UPLOAD => (bool)$safe,
        ]);
    }

    public function setVersion($version)
    {
        return $this->addOption(CURLOPT_HTTP_VERSION, $version);
    }

    public function setTimeout($value, $ms = true)
    {
        return $this->addOption($ms ? CURLOPT_TIMEOUT_MS : CURLOPT_TIMEOUT, $value);
    }

    public function setEncoding($value)
    {
        return $this->addOption(CURLOPT_ENCODING, $value);
    }

    public function setSSL($peer = false, $host = 2, array $extraOptions = [])
    {
        return $this->addOptions([CURLOPT_SSL_VERIFYPEER => $peer, CURLOPT_SSL_VERIFYHOST => $host])
                    ->addOptions($extraOptions);
    }


    public static function buildUrl($url, $params)
    {
        if (empty($params)) {
            return $url;
        }

        $info = parse_url($url);
        parse_str($info['query'], $query);
        $query = http_build_query(array_merge($query, $params));

        $info['query'] = $query;
        $url = http_build_url($url, $info);

        return $url;
    }


    /**
     * @param string $content
     * @param array $headers
     * @return Response
     */
    protected static function createResponse($content, $headers)
    {
        $response = (new HttpResponse())->setContent($content)->setHeaders($headers);
        return $response;
    }
}