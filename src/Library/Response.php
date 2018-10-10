<?php
namespace Helvetica\Standard\Library;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class Response extends GuzzleResponse
{
    /**
     * Sends HTTP headers.
     */
    public function sendHeaders()
    {
        if (!headers_sent()) {
            $statusCode = $this->getStatusCode();
            $protocolVersion = $this->getProtocolVersion();
            $statusText = $this->getReasonPhrase();
            foreach ($this->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header($name.': '.$value, false, $statusCode);
                }
            }
            $statusHeader = sprintf('HTTP/%s %s %s', $protocolVersion, $statusCode, $statusText);
            header($statusHeader, true, $statusCode);
        }
    }

    /**
     * Sends body for the current response.
     */
    public function sendBody()
    {
        $body = $this->getBody();
        $body->rewind();
        echo $body->getContents();
        return $this;
    }

    /**
     * Set Content-Type for a new response.
     * 
     * @param string $contentType
     * @return static
     */
    public function withContentType($contentType)
    {
        return $this->withHeader('content-type', $contentType);
    }

    /**
     * Set response content with json data.
     * 
     * @param array $data
     * 
     * @return static
     */
    public function withJson($data)
    {
        $new = $this->withContentType('application/json');
        return $new->withContent(json_encode($data));
    }

    /**
     * Set response content with content.
     * 
     * @param string $data
     * 
     * @return static
     */
    public function withContent($content)
    {
        $new = clone $this;
        $new->getBody()->write($content);
        return $new;
    }
}
