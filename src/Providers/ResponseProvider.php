<?php
namespace Helvetica\Standard\Providers;

use Helvetica\Standard\Net\Request;
use Helvetica\Standard\Net\Response;

class ResponseProvider
{
    /**
     * Make Response.
     * 
     * @param string $class
     * 
     * @return Response
     */
    public function __invoke($class)
    {
        $headers = $this->makeHeaders(Request::getAllHeaders());
        return new Response(200, $headers);
    }

    /**
     * Build response headers.
     * 
     * @param array $headers
     * @return array
     */
    private function makeHeaders($headers)
    {
        $responseHeaders = [];
        if (\array_key_exists('Accept', $headers)) {
            $responseHeaders['Content-Type'] = $headers['Accept'];
        }

        if (\array_key_exists('Accept-Language', $headers)) {
            $responseHeaders['Content-Language'] = $headers['Accept-Language'];
        }

        return $responseHeaders;
    }
}
