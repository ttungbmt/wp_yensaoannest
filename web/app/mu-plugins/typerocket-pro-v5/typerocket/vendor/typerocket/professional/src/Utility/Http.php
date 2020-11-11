<?php
namespace TypeRocketPro\Utility;

use TypeRocket\Utility\Data;

class Http
{
    /** @var null|string */
    protected $method = null;
    /** @var null|array */
    protected $headers = null;
    protected $responseHeaders = null;
    /** @var null|array */
    protected $data = null;
    /** @var null|string  */
    protected $url = null;
    protected $curl = null;

    /**
     * Http constructor.
     *
     * @param string $url
     * @param string $method
     */
    public function __construct($url, $method = 'GET')
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, function($curl, $header) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $this->responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $this->method($method);
        $this->url($url);
    }

    /**
     * @param null|mixed $curl
     *
     * @return $this|false|resource|null
     */
    public function &curl($curl = null)
    {
        if(func_num_args() == 0) {
            return $this->curl;
        }

        if(is_null($curl)) {
            curl_close($this->curl);
        }

        $this->curl = $curl;

        return $this;
    }

    /**
     * @param null|string $url
     *
     * @return $this|string|null
     */
    public function url($url = null)
    {
        if(func_num_args() == 0) {
            return $this->url;
        }

        $this->url = $url;

        curl_setopt($this->curl, CURLOPT_URL, $this->url);

        return $this;
    }

    /**
     * @param null|string $method
     *
     * @return $this|string|null
     */
    public function method($method = null)
    {
        if(func_num_args() == 0) {
            return $this->method;
        }

        $this->method = $method;

        if($this->method != 'GET') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
        }

        return $this;
    }

    /**
     * @param array|null $headers
     *
     * @return $this|array|null
     */
    public function headers($headers = null)
    {
        if(func_num_args() == 0) {
            return $this->headers;
        }

        if(is_array($headers)) {
            $this->headers = array_merge($this->headers ?? [], $headers);
        }

        if($this->headers) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        }

        return $this;
    }

    /**
     * @return null|array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @param array|null $data
     * @param bool $json
     *
     * @return $this|array|string|array
     */
    public function data($data = null, $json = false)
    {
        if(func_num_args() == 0) {
            return $this->data;
        }

        if($json) {
            $data = json_encode($data);
            $length = strlen($data);

            $this->headers([
                'Content-Type: application/json',
                'Content-Length: ' . $length
            ]);
        }

        $this->data = $data;

        if($this->data) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->data);
        }

        return $this;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function auth($username, $password)
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, urlencode($username).':'.urlencode($password));

        return $this;
    }

    /**
     * @param int $responseSeconds
     * @param int $connectSeconds
     *
     * @return $this
     */
    public function timeout($responseSeconds = 180, $connectSeconds = 30)
    {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $connectSeconds);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $responseSeconds);

        return $this;
    }

    /**
     * @param int|bool $option
     * @param mixed $value
     *
     * @link https://www.php.net/manual/en/function.curl-setopt.php
     *
     * @return $this
     */
    public function option($option, $value)
    {
        curl_setopt($this->curl, $option, $value);

        return $this;
    }

    /**
     * @param bool $keepAlive
     *
     * @return CurlResponse
     */
    public function exec($keepAlive = false)
    {
        $response = curl_exec($this->curl);

        if(Data::isJson($response)) {
            $response = json_decode($response, true);
        }

        $return = static::response($response, $this->curl, $this->responseHeaders);

        if(!$keepAlive) {
            $this->curl(null);
        }

        return $return;
    }

    /**
     * @param $response
     * @param $curl
     * @param array $responseHeaders
     *
     * @return CurlResponse
     */
    public static function response($response, $curl, $responseHeaders = []) : CurlResponse
    {
        return new CurlResponse(
            $response,
            $responseHeaders,
            curl_getinfo($curl, CURLINFO_HTTP_CODE),
            curl_getinfo($curl),
            curl_errno($curl)
        );
    }

    /**
     * @param string $url
     *
     * @return static
     */
    public static function get($url)
    {
        return (new static($url, 'GET'));
    }

    /**
     * @param string $url
     * @param null|array|string $data
     * @param bool $json
     *
     * @return $this
     */
    public static function post($url, $data = null, $json = false)
    {
        $http = (new static($url, 'POST'));
        if($data) { $http->data($data, $json); }

        return $http;
    }

    /**
     * @param string $url
     * @param null|array|string $data
     * @param bool $json
     *
     * @return $this
     */
    public static function put($url, $data = null, $json = false)
    {
        $http = (new static($url, 'PUT'));
        if($data) { $http->data($data, $json); }

        return $http;
    }

    /**
     * @param string $url
     * @param null|array|string $data
     * @param bool $json
     *
     * @return $this
     */
    public static function delete($url, $data = null, $json = false)
    {
        $http = (new static($url, 'DELETE'));
        if($data) { $http->data($data, $json); }

        return $http;
    }

}