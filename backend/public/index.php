<?php

date_default_timezone_set('UTC');

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;

$allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: '*';
header("Access-Control-Allow-Origin: {$allowedOrigin}");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = require dirname(__DIR__) . '/src/routes.php';

try {
    $router->dispatch(new Request());
} catch (Throwable $e) {
    Response::error('Erreur serveur: ' . $e->getMessage(), 500);
}
