<?php

require dirname(__DIR__) . '/core/bootstrap.php';

$app = DI\Bridge\Slim\Bridge::create();
$app->add(App\Middlewares\Auth::class);
$app->add(new RKA\Middleware\IpAddress());
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

require BASE_DIR . 'core/routes.php';

try {
    $app->run();
} catch (Throwable $e) {
    http_response_code($e->getCode());
    echo json_encode($e->getMessage());
}
