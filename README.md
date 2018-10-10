# Helvetica/Standard 
![GitHub](https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square)
![PyPI - Status](https://img.shields.io/pypi/status/Django.svg?style=flat-square)


#### Simple is Power

## Installation
```
composer require helvetica/standard 1.0.1
```

## Get Start
```php
require('vendor/autoload.php');

use Helvetica\Standard\App;
use Helvetica\Standard\Router;
use Helvetica\Standard\Library\Response;

$router = new Router();

$router->set('/hello/<name>', function(Response $response, $name) {
    return $response->withContent('hello ' . $name);
});

(new App)->start();
```
Test with built-in server
```
php -S localhost:8080
```
Visit http://localhost:8080/hello/world to say: "hello world" 

## Using template
>hello.html
```html
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        <h1>hello <?= $name ?></h1>
    </body>
</html>
```

>index.php
```php
use Helvetica\Standard\App;
use Helvetica\Standard\Router;
use Helvetica\Standard\Library\Template;
use Helvetica\Standard\Library\Response;

$router = new Router();

$router->set('/hello/<name>', function(Response $response, Template $temp, $name) {
    $output = $temp->render(__DIR__ . '/hello.php', ['name' => $name]);
    return $response->withContent($output);
});

(new App)->start();
```
Visit http://localhost:8080/hello/world to say: "hello world"

## Controller classes
> index.php
```php
use Helvetica\Standard\App;
use Helvetica\Standard\Router;
use Helvetica\Standard\Library\Response;

class PrintController
{
    public function hello(Response $response, $name)
    {
        return $response->withContent('hello ' . $name);
    }
}

$router = new Router();

$router->set('/hello/<name>', [PrintController::class, 'hello']);

(new App)->start();
```
