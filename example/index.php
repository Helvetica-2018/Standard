<?php
require('../vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    $class = str_replace('Helvetica/Standard', '../src', $class);
    require_once($class . '.php');
});

use Helvetica\Standard\App;
use Helvetica\Standard\Router;
use Helvetica\Standard\Library\Request;
use Helvetica\Standard\Library\Response;
use Helvetica\Standard\Abstracts\ActionFilter;

class SayHelloFilter extends ActionFilter
{
    public function hook(Closure $next, $request)
    {
        $params = $this->getParams();
        $name = $params['name'];
        $request->withAttributes(['text' => 'hello ' . $name]);
        return $next($request);
    }
}

$router = new Router();

$router->set('/hello/<name>', function(Request $request, Response $response, $name) {
    $text = $request->getAttribute('text');
    return $response->withContent($text);
})->setFilters([SayHelloFilter::class]);

(new App)->start();