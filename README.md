# Helvetica/Standard
[![star](https://gitee.com/helvetica/standard/badge/star.svg?theme=white)](https://gitee.com/helvetica/standard/stargazers)

#### Simple is Power

## Installation
>composer require helvetica/standard v1.0.0

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
