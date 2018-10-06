<?php
namespace Helvetica\Standard\Exception;

/**
 * Abort 404 http code by default.
 */
class MethodNotAllowedException extends \Exception
{
    /**
     * @param string Exception message.
     */
    public function __construct($message = '405 method not allowed.')
    {
        parent::__construct($message, 405);
        http_response_code(405);
    }
}
