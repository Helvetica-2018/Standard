<?php
namespace Helvetica\Standard\Library;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class Request extends GuzzleRequest
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PURGE = 'PURGE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /**
     * Customer attributes
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Add customer attributes.
     * 
     * @param array $attributes
     * @return static
     */
    public function withAttributes($attributes)
    {
        $this->attributes = \array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Get customer attributes.
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get a custome attribute or return default value.
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        $attributes = $this->getAttributes();
        if (\array_key_exists($key, $attributes)) {
            return $attributes[$key];
        }
        return $default;
    }

    /**
     * It could be useful if you using nginx instead of apache 
     * 
     * {@link http://www.php.net/manual/en/function.getallheaders.php}
     * 
     * @return array
     */
    public static function getAllHeaders()
    {
        if (!function_exists('getallheaders')) {
            $headers = []; 
            foreach ($_SERVER as $name => $value)  { 
                if (substr($name, 0, 5) == 'HTTP_') { 
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
                } 
            } 
            return $headers;
        }
        return getallheaders();
    }

    /**
     * Create request from env.
     * 
     * @param StreamInterface $body
     */
    public function __construct(StreamInterface $body)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        $headers = static::getAllHeaders();
        parent::__construct($method, new Uri($url), $headers, $body);
    }

    /**
     * Get the query params of the URI.
     * 
     * @return array
     */
    public function getQueryParams()
    {
        $query = $this->getUri()->getQuery();
        \parse_str($query, $params);
        return $params;
    }

    /**
     * Get the params of the body.
     * 
     * @return array
     */
    public function getBodyParams()
    {
        $content = $this->getBody()->getContents();
        if ($this->hasHeader('content-type')) {
            $contentType = $this->getHeader('content-type');
            if ($contentType == 'application/json') {
                $content = \utf8_encode($content);
                return \json_decode($content, true);
            }
        }
        \parse_str($content, $params);
        return $params;
    }

    /**
     * Get params from query and body.
     * 
     * @return array
     */
    public function getParams()
    {
        $queryParams = $this->getQueryParams();
        $bodyParams = $this->getBodyParams();
        return \array_merge_recursive($queryParams, $bodyParams);
    }

    /**
     * Get one param.
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $params = $this->getParams();
        if (\array_key_exists($key, $params)) {
            return $params[$key];
        }
        return $default;
    }

    /**
     * Converts a string array into a utf8 encoded string array
     * {@link http://php.net/manual/zh/function.utf8-encode.php#112497}
     * 
     * @param array $array
     * @return array
     */
    private function utf8StringArrayEncode(&$array){
        $func = function(&$value, &$key) {
            if(\is_string($value)){
                $value = \utf8_encode($value);
            } 
            if(\is_string($key)){
                $key = \utf8_encode($key);
            }
            if(\is_array($value)){
                $this->utf8StringArrayEncode($value);
            }
        };
        \array_walk($array,$func);
        return $array;
    }
}
