<?php
namespace Helvetica\Standard\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerOverrideException extends \RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param string $id Identifier of the store entry
     */
    public function __construct($id)
    {
        parent::__construct("Cannot override store offset \"$id\".");
    }
}
