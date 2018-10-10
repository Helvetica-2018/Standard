<?php
namespace Helvetica\Standard\Abstracts;

use Helvetica\Standard\Di;

/**
 * @property Di $di
 */
abstract class Provider
{
    /**
     * Init dependents provider.
     * 
     * @param Container $dependent
     */
    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    /**
     * Register dependents providers.
     * 
     * @return void
     */
    abstract public function register();
}
