<?php
namespace Helvetica\Standard;

use Closure;
use GuzzleHttp\Psr7\Request;
use Helvetica\Standard\Exception\NotFoundException;

class Router
{
    /**
     * Params on url.
     * 
     * @var array
     */
    public $params = [];

    /**
     * The routes storage.
     *
     * @var array
     */
    public static $routes = [];

    /**
     * The classmap namespace. if false not use.
     *
     * @var boolean
     */
    protected static $mapNamespace = false;

    /**
     * routes paths s prefix.
     *
     * @var string
     */
    protected $pathPrefix = '';

    /**
     * Before action filters for current router.
     *
     * @var array
     */
    public $filters = [];

    /**
     * Router callback.
     *
     * @var \Closure|array
     */
    protected $controller;

    /**
     * Match route fields with request.
     * 
     * @return static|false
     */
    public static function match()
    {
        foreach (static::$routes as $path => $route) {
            $result = static::matchHttpRequest($path);
            if ($result !== false) {
                $route->params = $result;
                return $route;
            }
        }
        throw new NotFoundException();
    }

    /**
     * Match route storage by request url and return params.
     * 
     * @param string $url
     * 
     * @return array|false
     */
    private static function matchHttpRequest($pattern)
    {
        $pattern = static::replaceRegexpKeyWorlds($pattern);
        $pathInfo = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO']: '/';

        $patternRegexp = static::replacePatternKeyword($pattern);
        $regexp = '/^'. $patternRegexp .'\/?$/u';
        if (\preg_match($regexp, $pathInfo, $matched)) {
            \preg_match_all('/\<(.*?)\>/iu', $pattern, $attributes);
            foreach ($attributes[1] as $num => $key) {
                $result[$key] = $matched[$num + 1];
            }
            return $result ? $result : [];
        }
        return false;
    }

    /**
     * Replace the regex key words and return.
     * 
     * @param string $pattern
     * 
     * @return string
     */
    private static function replacePatternKeyword($pattern)
    {
        $customKeyword = [
            '/\//' => '\\/',
            '/\<(.*?)\>/' => '(.*?)'
        ];

        return preg_replace(
            \array_keys($customKeyword),
            \array_values($customKeyword),
            $pattern
        );
    }

    private static function replaceRegexpKeyWorlds($pattern)
    {
        $regexKeywords = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)'
        ];
        return str_replace(
            \array_keys($regexKeywords),
            \array_values($regexKeywords),
            $pattern
        );
    }

    /**
     * Set a field.
     *
     * @param string $path
     * @param Closure|array $closure
     * @return void
     */
    public function set($path, $controller)
    {
        $key = $this->pathPrefix . $path;
        $router = clone $this;
        $router->controller = $controller;
        static::$routes[$key] = $router;
        return $router;
    }

    /**
     * Has field?
     *
     * @param string $path
     * @return boolean
     */
    public function has($path)
    {
        return array_key_exists($path, static::$routes);
    }

    /**
     * Delete a field.
     *
     * @param string $path
     * @return void
     */
    public function delete($path)
    {
        unset(static::$routes[$path]);
    }

    /**
     * Get a field.
     *
     * @param string $path
     * @return Closure|null
     */
    public function get($path)
    {
        if ($this->has($path)) {
            $closure = static::$routes[$path];
            return $closure;
        }
        return null;
    }

    /**
     * Set route name.
     * 
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the class map namespace.
     *
     * @param string $namespace
     * @return void
     */
    public static function classmap($namespace)
    {
        static::$mapNamespace = $namespace;
    }

    /**
     * Set route group.
     *
     * @param string $path
     * @param Closure $closure
     * @return self
     */
    public function group($path, Closure $closure)
    {
        $router = new static();
        $router->pathPrefix = $path;
        \call_user_func($closure->bindTo($router));
    }

    /**
     * Set before action filters.
     *
     * @param array $filters
     * @return self
     */
    public function setFilters(array $filters = [])
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Get $filters
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get url params
     * 
     * @return array
     *
     * @return void
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get controller.
     *
     * @return \Closure|array
     */
    public function getController()
    {
        return $this->controller;
    }
}
