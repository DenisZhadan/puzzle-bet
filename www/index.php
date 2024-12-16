<?php

use Sparksoft\Bet\controller\BetController;
use Sparksoft\Bet\controller\ClientController;
use Sparksoft\Bet\controller\MatchController;
use Sparksoft\Bet\route\Route;

require '../vendor/autoload.php';

$controllers = [
    new BetController(),
    new ClientController(),
    new MatchController()
];

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$found = false;

foreach ($controllers as $controller) {
    $reflection = new ReflectionClass($controller);
    foreach ($reflection->getMethods() as $method) {
        $attributes = $method->getAttributes(Route::class);
        foreach ($attributes as $attribute) {
            $route = $attribute->newInstance();
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route->path);
            $pattern = str_replace('/', '\/', $pattern);
            if (preg_match('/^' . $pattern . '$/', $requestUri, $matches) && $requestMethod === $route->method) {
                try {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $method->invokeArgs($controller, $params);
                } catch (\Throwable $throwable) {
                    header("HTTP/1.0 400 Bad Request");
                    echo json_encode(['message' => $throwable->getMessage()]);
                }
                $found = true;
                break 3;
            }
        }
    }
}

if (!$found) {
    header("HTTP/1.0 404 Not Found");
    echo json_encode(['message' => 'Endpoint not found']);
}
