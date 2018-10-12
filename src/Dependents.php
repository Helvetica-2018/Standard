<?php
namespace Helvetica\Standard;

use Helvetica\Standard\App;
use GuzzleHttp\Psr7\Stream;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;
use Helvetica\Standard\Abstracts\Provider;
use Helvetica\Standard\Library\Environment;
use Helvetica\Standard\Handlers\HttpExceptionHandler;
use Helvetica\Standard\Exception\NotFoundException;
use Helvetica\Standard\Exception\MethodNotAllowedException;

/**
 * @property \Helvetica\Standard\Dependent $dependent
 */
class Dependents extends Provider
{
    /** @var \Helvetica\Standard\Dependent */
    protected $dependent;

    /**
     * Register system dependents.
     */
    public function register()
    {
        if (! $this->dependent->hasProvider(Request::class)) {
            $this->dependent->setProvider(Request::class, function() {
                $stream = new Stream(fopen('php://input', 'r'));
                return new Request($stream);
            });
        }

        if (! $this->dependent->hasProvider(Response::class)) {
            $this->dependent->setProvider(Response::class, function() {
                $headers = ['Content-Type' => 'text/html; charset=UTF-8'];
                return new Response(200, $headers);
            });
        }

        if (! $this->dependent->hasProvider(Environment::class)) {
            $this->dependent->setProvider(Environment::class, function() {
                return new Environment($_SERVER);
            });
        }

        if (! $this->dependent->hasProvider(App::HANDLE_NOT_FOUND)) {
            $this->dependent->setProvider(App::HANDLE_NOT_FOUND, function($e) {
                $handler = new HttpExceptionHandler();
                $handler->exception = $e;
                return $handler;
            });
        }
    }
}
