<?php
namespace Helvetica\Standard\Handlers;

use Helvetica\Standard\Abstracts\StandardExceptionHandler;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;

class HttpExceptionHandler extends StandardExceptionHandler
{
    /** @var \Exception */
    public $exception;

    /**
     * Get response
     * This method is injectable
     *
     * @param Request $request
     * @param Response $response
     * 
     * @return Response
     */
    public function hook(Request $request, Response $response)
    {
        return $this->exception->getResponse($request, $response);
    }
}