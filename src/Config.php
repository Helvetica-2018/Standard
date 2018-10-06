<?php
namespace Helvetica\Standard;

use Helvetica\Standard\Net\Request;
use Helvetica\Standard\Net\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Helvetica\Standard\Providers\RequestProvider;
use Helvetica\Standard\Providers\ResponseProvider;

class Config
{
    protected $customer = [
        'providers' => []
    ];

    /**
     * @param array $customer
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Set dependents providers.
     * 
     * @return array
     */
    public function providers()
    {
        $providers = \array_key_exists('providers', $this->customer) ? $this->customer['providers'] : [];
        return \array_merge($providers, [
            Request::class => RequestProvider::class,
            RequestInterface::class => RequestProvider::class,
            Response::class => ResponseProvider::class,
            ResponseInterface::class => ResponseProvider::class,
        ]);
    }
}
