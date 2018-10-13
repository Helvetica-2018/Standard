<?php
namespace Helvetica\Standard\Abstracts;

use Closure;

abstract class ActionFilter
{
    /**
     * Path info params.
     * 
     * @var array $params
     */
    public $params = [];

    /**
     * Get params from path info.
     * 
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }
}
