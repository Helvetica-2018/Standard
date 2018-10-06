<?php
namespace Helvetica\Standard\Providers;

use GuzzleHttp\Psr7\Stream;
use Helvetica\Standard\Net\Request;

class RequestProvider
{
    /**
     * Make Request.
     * 
     * @param string $class
     * 
     * @return Request
     */
    public function __invoke($class)
    {
        $stream = new Stream(fopen('php://input', 'r'));
        return new Request($stream);
    }
}
