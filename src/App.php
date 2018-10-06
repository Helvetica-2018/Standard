<?php
namespace Helvetica\Standard;

use Closure;
use GuzzleHttp\Psr7\Stream;
use Helvetica\Standard\Dependent;
use Helvetica\Standard\Net\Request;
use Helvetica\Standard\Net\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * @property Router $router
 * @property Container $container
 * @property Ioc $ioc
 * @property Config $config
 */
class App
{
    /** @var Container */
    public $container;

    /** @var Router $router */
    public $router;

    /** @var Ioc $ioc */
    public $ioc;

    /** @var Config */
    public $config;

    /** @var Router $route */
    private $route;

    /**
     * This method is part of the Symfony
     * {@link https://github.com/symfony/http-foundation/blob/master/Response.php#L1193} 
     * 
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     */
    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->router = new Router();
        $this->container = new Container();
        $this->ioc = new Ioc($this->container);
        $this->config = new Config($config);
        $this->setProviders($this->config->providers());
    }

    public function setProviders($providers)
    {
        Dependent::setProviders($providers);
    }

    /**
     * Start request action.
     * Send response.
     */
    public function start()
    {
        $route = Router::match();
        $this->route = $route;
        $response = $this->startAction();
        if ($response instanceof ResponseInterface) {
            $this->accept($response);
        }
    }

    /**
     * Start a request action.
     * happy laravel magic :)
     * 
     * @param Router $route
     * @return Response
     */
    private function startAction()
    {
        $classes = $this->route->getFilters();
        $filters = $this->prepareFilters($classes);
        $stack = \array_reduce($filters, $this->carry(), $this->controllerWrapper());
        return $stack($this->prepareRequest());
    }

    /**
     * Prepare http request from env.
     * 
     * @return \Helvetica\Standard\Net\Request
     */
    private function prepareRequest()
    {
        return $this->ioc->newClass(Request::class)->getInstance();
    }

    /**
     * Build filter closures.
     * @param array $filterClasses
     * @return Closure[]
     */
    private function prepareFilters($filterClasses)
    {
        $filters = \array_map(function($class) {
            $mock = $this->ioc->newClass($class);
            $mock->setStaticProperty('params', $this->route->params);
            return $mock->getClosure('hook');
        }, $filterClasses);
        return \array_reverse($filters);
    }

    /**
     * Build a filter for controller.
     * 
     * @return Closure
     * 
     * @throws \RuntimeException
     */
    private function controllerWrapper()
    {
        $route = $this->route;
        return function (Request $request) use ($route) {
            if (\method_exists($route->callback, '__invoke')) {
                return $this->ioc->call($route->callback, $route->params);
            } elseif (\is_array($route->callback)) {
                $class = $route->callback[0];
                $method = $route->callback[1];
                $controller = $this->ioc->newClass($class);
                return $controller->injection($method, $route->params);
            }
            throw new \RuntimeException('The controller option must be array or instanceof \Closure.');
        };
    }

    /**
     * Build filters to stack.
     * 
     * @return Closure
     */
    private function carry()
    {
        return function($next, $filter) {
            return function ($request) use ($next, $filter) {
                return $filter($next, $request);
            };
        };
    }

    /**
     * Send response.
     * 
     * @param Response $response
     */
    private function accept(Response $response)
    {
        $response->sendHeaders();
        $response->sendBody();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            static::closeOutputBuffers(0, true);
        }
    }
}
