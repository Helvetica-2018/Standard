<?php
namespace Helvetica\Standard\Handlers;

use Helvetica\Standard\Abstracts\HttpExceptionHandler;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;

class NotFoundHandler extends HttpExceptionHandler
{
    /**
     * Get response
     * This method is injectable
     *
     * @param Request $request
     * @param Response $response
     * 
     * @return Response
     */
    public function getResponse(Request $request, Response $response)
    {
        return $this->exception->getResponse($request, $response);
    }
}