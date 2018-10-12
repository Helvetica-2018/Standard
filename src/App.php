<?php
namespace Helvetica\Standard;

use Closure;
use GuzzleHttp\Psr7\Stream;
use Helvetica\Standard\Dependent;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;
use Helvetica\Standard\Abstracts\Provider;
use Helvetica\Standard\Handlers\HttpExceptionHandler;
use Helvetica\Standard\Exception\HttpException;
use Helvetica\Standard\Exception\NotFoundException;
use Helvetica\Standard\Exception\MethodNotAllowedException;

/**
 * @property Router $router
 * @property Dependent $dependent
 * @property Config $config
 */
class App
{
    const HANDLE_NOT_FOUND = 'HANDLE_NOT_FOUND';

    const HANDLE_METHOD_NOT_ALLOW = 'HANDLE_METHOD_NOT_ALLOW';

    /** @var Dependent $dependent */
    public $dependent;

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
        $this->dependent = new Dependent([
            Config::class => new Config($config)
        ]);
    }

    /**
     * Register a provider.
     * 
     * @param string $provider
     */
    public function registerProvider($provider)
    {
        (new $provider($this->dependent))->register();
    }

    /**
     * Start request action.
     * Send response.
     * 
     * @return void
     */
    public function start()
    {
        try {
            $this->registerProvider(Dependents::class);
            $route = Router::match();
            $response = $this->startAction($route);
        }
        catch (\Throwable $e) {
            $response = $this->handleException($e);
        }
        catch (\Exception $e) {
            $response = $this->handleException($e);
        }

        if ($response instanceof Response) {
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
    private function startAction($route)
    {
        $classes = $route->getFilters();
        $params = $route->getParams();
        $controller = $route->getController();

        $filters = $this->prepareFilters($classes, $params);
        $stack = \array_reduce(
            $filters,
            $this->carry(),
            $this->controllerWrapper($controller, $params)
        );
        
        $request = $this->dependent->get(Request::class);
        return \call_user_func($stack, $request);
    }

    /**
     * Build filter closures.
     * 
     * @param array $filterClasses
     * @param array $params
     * @return Closure[]
     */
    private function prepareFilters($filterClasses, $params)
    {
        $filters = \array_map(function($class) use ($params) {
            $filter = $this->dependent->get($class);
            $filter->params = $params;
            return $this->dependent->methodToClosure($filter, 'hook');
        }, $filterClasses);
        return \array_reverse($filters);
    }

    /**
     * Build a filter for controller.
     * 
     * @param Router $route
     * 
     * @return Closure
     * @throws \RuntimeException
     */
    private function controllerWrapper($controller, $params)
    {
        return function (Request $request) use ($controller, $params) {
            if (\method_exists($controller, '__invoke')) {
                return $this->dependent->subCall($controller, $params);
            } elseif (\is_array($controller)) {
                $class = $controller[0];
                $method = 'index';
                if (count($controller) > 1) {
                    $method = $controller[1];
                }

                $controller = $this->dependent->get($class);
                return $this->dependent->methodCallWithInstance($controller, $method, $params);
            }
            throw new \RuntimeException('The param 2 must be array or callable object.');
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

    /**
     * Set handler.
     *
     * @param string $handleName
     * @param string $handler
     * @return void
     */
    public function setHandler($handleName, $handler)
    {
        $this->dependent->setProvider($handleName, function() use ($handler) {
            return new $handler();
        });
    }

    /**
     * Global exception handler.
     * 
     * @param \Exception|\Error $e
     * 
     * @return Response
     * 
     * @throws Exception
     */
    private function handleException($e)
    {
        if ($e instanceof HttpException) {

            if ($e instanceof NotFoundException) {
                return $this->injectionHttpExceptionHandler(static::HANDLE_NOT_FOUND, $e);
            }

            if ($e instanceof MethodNotAllowedException) {
                return $this->injectionHttpExceptionHandler(static::HANDLE_METHOD_NOT_ALLOW, $e);
            }

            return $this->dependent->methodCallWithInstance($e, 'getResponse');
        }

        throw $e;
    }

    /**
     * Inject HttpExceptionHandler.
     * 
     * @param object $handleName
     * @param Throwable $exception
     * 
     * @return Response
     */
    private function injectionHttpExceptionHandler($handleName, $exception)
    {
        $handler = $this->dependent->getProvider($handleName);
        $instance = \call_user_func($handler, $exception);
        return $this->dependent->methodCallWithInstance($instance, 'hook');
    }
}
