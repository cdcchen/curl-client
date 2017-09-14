<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 15/5/4
 * Time: 下午5:58
 */

namespace cdcchen\net\curl;


/**
 * Class Request
 * @package cdcchen\net\curl
 */
class CurlClient
{
    /**
     * @var bool
     */
    public $debug = false;

    /**
     * @var array
     */
    protected static $defaultOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_DNS_USE_GLOBAL_CACHE => true,
        CURLOPT_FORBID_REUSE => true,
    ];

    /**
     * @var array
     */
    private $_options = [];
    /**
     * @var string
     */
    private $_url;

    /**
     * @var array
     */
    private $_transferInfo;


    /**
     * Request constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->addDefaultOptions()->addOptions($options);
    }

    /**
     * @param bool $value
     * @return static
     */
    public function setDebug(bool $value): self
    {
        $this->debug = (bool)$value;
        return $this->addOption(CURLOPT_VERBOSE, $this->debug);
    }

    /**
     * @param array $options
     * @return static
     */
    public function setOptions(array $options): self
    {
        return $this->clearOptions()->addOptions($options);
    }

    /**
     * @return static
     */
    private function addDefaultOptions(): self
    {
        return $this->addOptions(static::$defaultOptions);
    }

    /**
     * @param int $option
     * @param mixed $value
     * @return static
     */
    public function addOption(int $option, $value): self
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * @param array $options
     * @return static
     */
    public function addOptions(array $options): self
    {
        foreach ($options as $option => $value) {
            $this->addOption($option, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->_options;
    }

    /**
     * @param int $option
     * @return static
     */
    public function removeOption(int $option): self
    {
        unset($this->_options[$option]);
        return $this;
    }

    /**
     * @param array $options
     * @return static
     */
    public function removeOptions(array $options): self
    {
        foreach ($options as $option) {
            unset($this->_options[$option]);
        }

        return $this;
    }

    /**
     * @param bool $setDefaultOptions
     * @return static
     */
    public function resetOptions(bool $setDefaultOptions = true): self
    {
        $this->clearOptions();
        if ($setDefaultOptions) {
            $this->addOptions(static::$defaultOptions);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clearOptions(): self
    {
        $this->_options = [];
        return $this;
    }

    /**
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): self
    {
        $this->_url = $url;
        return $this->addOption(CURLOPT_URL, $url);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->_url;
    }

    /**
     * @param array $options
     * @param bool $merge
     */
    public static function setDefaultOptions(array $options, bool $merge = false): void
    {
        if ($merge) {
            foreach ($options as $option => $value) {
                static::$defaultOptions[$option] = $value;
            }
        } else {
            static::$defaultOptions = $options;
        }
    }

    /**
     * @param null|int $option
     * @return array|mixed|null
     */
    public static function getDefaultOptions(int $option = null)
    {
        if ($option === null) {
            return static::$defaultOptions;
        }

        return static::$defaultOptions[$option] ?? null;
    }

    /**
     * @param array $options
     * @param array $options1
     * @return array
     */
    public static function mergeOptions(array $options, array $options1): array
    {
        foreach ($options1 as $index => $value) {
            $options[$index] = $value;
        }

        return $options;
    }

    /**
     * @return bool|string
     * @throws RequestException
     */
    public function send()
    {
        $handle = curl_init();
        if (!$this->beforeRequest($this, $handle)) {
            return false;
        }

        $this->prepare();
        $this->addOption(CURLOPT_VERBOSE, $this->debug);

        curl_setopt_array($handle, $this->getOptions());

        $content = curl_exec($handle);

        // check cURL error
        $errorNumber = curl_errno($handle);
        $errorMessage = curl_error($handle);
        $this->_transferInfo = curl_getinfo($handle);
        $this->afterRequest($this, $handle);
        curl_close($handle);

        if ($errorNumber !== CURLE_OK) {
            throw new RequestException('Curl error: #' . $errorNumber . ' - ' . $errorMessage, $errorNumber);
        }

        return $content;
    }

    /**
     * prepare request params
     */
    protected function prepare(): void
    {
    }

    /**
     * @param array $requests
     */
    public function batchExecute(array $requests)
    {
    }

    /**
     * @param CurlClient $client
     * @param resource $handle curl_init resource
     * @return bool
     */
    protected function beforeRequest(CurlClient $client, $handle): bool
    {
        return true;
    }

    /**
     * @param CurlClient $request
     * @param resource $handle
     */
    protected function afterRequest(CurlClient $request, $handle)
    {
    }

    /**
     * @param null|string $option
     * @return array|mixed
     */
    public function getTransferInfo($option = null)
    {
        if ($option === null) {
            return $this->_transferInfo;
        }

        return $this->_transferInfo[$option] ?? null;
    }
}