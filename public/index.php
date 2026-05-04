<?php

use Core\Request;
use Core\App;

header('Access-Control-Allow-Origin: http://frontend.local');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Автозагрузчик классов
 * 
 * @param string $class Полное имя класса с пространством имён
 * @return void
 */
spl_autoload_register(function (string $class): void {
    $class = ltrim($class, '\\');

    $map = [
        'Core\\' => __DIR__ . '/../core/',
        'app\Controllers\\' => __DIR__ . '/../app/Controllers/',
        'app\Models\\' => __DIR__ . '/../app/Models/',
        'app\Services\\' => __DIR__ . '/../app/Services/',
    ];

    foreach ($map as $namespace => $dir) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});


$request = new Request($_SERVER, $_GET, $_POST, $_FILES);


$app = new App();
$response = $app->getRouter()->dispatch($request);

$response->send();
