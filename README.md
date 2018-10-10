# Helvetica/Standard 
![GitHub](https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square)
![PyPI - Status](https://img.shields.io/pypi/status/Django.svg?style=flat-square)


#### Simple is Power

## Installation
>composer require helvetica/standard 1.0.1

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

>php -S localhost:8080

http://localhost:8080/hello/world
