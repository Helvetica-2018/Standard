<?php
namespace Helvetica\Standard\Abstracts;

use Helvetica\Standard\Dependent;

/**
 * @property Dependent $dependent
 */
abstract class Provider
{
    /**
     * Init dependents provider.
     * 
     * @param Dependent $dependent
     */
    public function __construct(Dependent $dependent)
    {
        $this->dependent = $dependent;
    }

    /**
     * Register dependents providers.
     * 
     * @return void
     */
    abstract public function register();
}
