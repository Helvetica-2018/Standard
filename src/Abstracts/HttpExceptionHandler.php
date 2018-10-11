<?php
namespace Helvetica\Standard\Abstracts;

use Helvetica\Standard\Library\Response;

/**
 * @method Response getResponse()
 */
abstract class HttpExceptionHandler
{
    /** @var \Exception */
    public $exception;
}
