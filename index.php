<?php
require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    $class = str_replace('Helvetica/Standard', 'src', $class);
    require_once($class . '.php');
});

use Helvetica\Standard\App;
use Helvetica\Standard\Container;
use Helvetica\Standard\Router;
use Helvetica\Standard\Ioc;
use Helvetica\Standard\Abstracts\ActionFilter;
use Helvetica\Standard\Net\Request;
use Helvetica\Standard\Net\Response;
use Helvetica\Standard\Abstracts\Config;
use GuzzleHttp\Psr7\Stream;

$router = new Router();

class Test
{
    public function index()
    {
        return 333;
    }
}

class Contr
{
    public function index(Test $test, $id, $cc)
    {
        return $test->index();
    }
}

class Mid1 extends ActionFilter
{
    public function hook(Closure $next, $request)
    {
        $request = $request->withAttributes(['userName' => $this->getParams()]);
        // print_r($request->getAttributes());
        return $next($request);
    }
}

class Mid2 extends ActionFilter
{
    public function hook(Closure $next, $request)
    {
        $response = $next($request);
        return $response;
    }
}


$app = new App();

$app->router->group('/test', function() {

    $this->set('/test1/<id>/aaa/<cc>', function(Request $r, Response $res, $id, $cc) {
        $data = $r->getAttributes();
        $res = $res->withJson($data);
        return $res;
    })->setFilters([Mid1::class, Mid2::class]);

});

$app->start();
