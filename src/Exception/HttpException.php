<?php
namespace Helvetica\Standard\Exception;

use Exception;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;

/**
 * This Exception is throw when application need to return a response.
 */
class HttpException extends Exception
{
    /**
     * Get response
     * This method is injectable
     *
     * @param Request $request
     * @param Response $response
     * 
     * @return Response
     */
    public function getResponse(Request $request, Response $response)
    {
        $code = $this->getCode();
        $message = $this->getMessage();
        $response = $response->withStatus($code);
        $accept = $request->getHeader('Accept');

        if ('application/json' == $accept) {
            $text = \json_encode(['code' => $code, 'message' => $message, 'file' => $this->getFile()]);
            $response = $response->withHeader('Content-Type', $accept);
        } else {
            $text = $this->renderHtml();
        }
        $response->getBody()->write($text);

        return $response;
    }

    /**
     * Render html type message.
     * 
     * @return string
     */
    protected function renderHtml()
    {
        $code = $this->getCode();
        $message = $this->getMessage();
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>http error $code</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body style="margin:0;padding:0;">
            <h2 style="text-align:center;border-bottom:1px solid #FFB6C1;padding-bottom:2rem;">
            {$message}
            </h2>
        </body>
        </html>
HTML;
        return $html;
    }
}
