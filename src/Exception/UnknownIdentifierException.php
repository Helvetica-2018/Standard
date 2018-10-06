<?php
namespace Helvetica\Standard\Exception;

/**
 * The unknown identifier exception.
 */
class UnknownIdentifierException extends \InvalidArgumentException
{
    /**
     * @param string $id The unknown identifier
     */
    public function __construct($id)
    {
        parent::__construct("Identifier \"$id\" is not defined.");
    }
}
