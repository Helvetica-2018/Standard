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
     * The hook return a response or call next hook.
     * 
     * @param Closure $next
     * @return Response
     */
    abstract public function hook(Closure $next, $request);

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
