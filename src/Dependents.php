<?php
namespace Helvetica\Standard;

use GuzzleHttp\Psr7\Stream;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;
use Helvetica\Standard\Abstracts\Provider;
use Helvetica\Standard\Library\Environment;
use Helvetica\Standard\Exception\NotFoundException;
use Helvetica\Standard\Exception\MethodNotAllowedException;

/**
 * @property \Helvetica\Standard\Di $di
 */
class Dependents extends Provider
{
    /** @var Di */
    protected $di;

    /**
     * Register system dependents.
     */
    public function register()
    {
        // Helvetica\Standard\Library\Request
        $this->di->setProvider(Request::class, function() {
            $stream = new Stream(fopen('php://input', 'r'));
            return new Request($stream);
        });

        // Helvetica\Standard\Library\Response
        $this->di->setProvider(Response::class, function() {
            $headers = ['Content-Type' => 'text/html; charset=UTF-8'];
            return new Response(200, $headers);
        });

        if (! $this->di->has(Environment::class)) {
            $this->di->setProvider(Environment::class, function() {
                return new Environment($_SERVER);
            });
        }
    }
}
