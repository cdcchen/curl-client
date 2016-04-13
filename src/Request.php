<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/4
 * Time: 下午5:58
 */

namespace cdcchen\net\curl;


class Request
{
    /**
     * @var bool
     */
    public $debug = false;

    protected static $defaultOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_DNS_USE_GLOBAL_CACHE => true,
        CURLOPT_FORBID_REUSE => true,
    ];

    private $_options = [];
    private $_url;

    /**
     * @var array
     */
    private $_transferInfo;


    public function __construct($options = [])
    {
        $this->addDefaultOptions()->addOptions($options);
    }

    public function setDebug($value = false)
    {
        $this->debug = (bool)$value;
        return $this->addOption(CURLOPT_VERBOSE, $this->debug);
    }

    public function setOptions(array $options)
    {
        return $this->clearOptions()->addOptions($options);
    }

    private function addDefaultOptions()
    {
        return $this->addOptions(static::$defaultOptions);
    }

    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    public function addOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->addOption($option, $value);
        }

        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function removeOptions($options)
    {
        $options = (array)$options;
        foreach ($options as $option) {
            unset($this->_options[$option]);
        }

        return $this;
    }

    public function resetOptions($setDefaultOptions = true)
    {
        $this->clearOptions();
        if ($setDefaultOptions) {
            $this->addOptions(static::$defaultOptions);
        }

        return $this;
    }

    public function clearOptions()
    {
        $this->_options = [];
        return $this;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this->addOption(CURLOPT_URL, $url);
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public static function setDefaultOptions(array $options, $append = false)
    {
        if ($append) {
            foreach ($options as $option => $value) {
                static::$defaultOptions[$option] = $value;
            }
        } else {
            static::$defaultOptions = $options;
        }
    }

    public static function getDefaultOptions($option = null)
    {
        return ($option === null) ? static::$defaultOptions : static::$defaultOptions[$option];
    }

    public static function mergeOptions(array $options, array $options1)
    {
        foreach ($options1 as $index => $value) {
            $options[$index] = $value;
        }

        return $options;
    }

    public function send()
    {
        $handle = curl_init();
        if (!$this->beforeRequest($this, $handle)) {
            return false;
        }

        $this->prepare();
        $this->addOption(CURLOPT_VERBOSE, $this->debug);

        $headers = [];
        $this->setHeaderOutput($this, $headers);
        curl_setopt_array($handle, $this->getOptions());
        $content = curl_exec($handle);

        // check cURL error
        $errorNumber = curl_errno($handle);
        $errorMessage = curl_error($handle);
        $this->_transferInfo = curl_getinfo($handle);
        $this->afterRequest($this, $handle);
        curl_close($handle);

        if ($errorNumber !== CURLE_OK) {
            throw new \Exception('Curl error: #' . $errorNumber . ' - ' . $errorMessage);
        }

        return static::createResponse($content, $headers);
    }

    public function prepare()
    {
    }

    public function batchExecute(array $requests)
    {
    }

    protected function beforeRequest(Request $request, $handle)
    {
        return true;
    }

    protected function afterRequest(Request $request, $handle)
    {
    }

    private function setHeaderOutput(Request $request, array &$output)
    {
        $request->addOption(CURLOPT_HEADERFUNCTION, function ($handle, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if (strlen($header) > 0) {
                $output[] = $header;
            }
            return mb_strlen($headerString, '8bit');
        });
    }

    /**
     * @param string $content
     * @param array $headers
     * @return Response
     */
    protected static function createResponse($content, $headers)
    {
        return (new Response())->setContent($content)->setHeaders($headers);
    }

    public function getTransferInfo($opt = null)
    {
        return $opt === null ? $this->_transferInfo : $this->_transferInfo[$opt];
    }
}