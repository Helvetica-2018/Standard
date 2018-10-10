<?php
namespace Helvetica\Standard\Exception;

use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Abort 404 http error.
 */
class NotFoundException extends HttpException
{
    /** @var int */
    protected $code = 404;

    /** @var string */
    protected $message = '404 Not Found';

}
