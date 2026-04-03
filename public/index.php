<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

// Simple Router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home/index';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = isset($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$methodName = isset($url[1]) ? $url[1] : 'index';

$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName($conn);

    if (method_exists($controller, $methodName)) {
        // Pass any remaining URL parts as arguments
        $params = array_slice($url, 2);
        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "<h2 style='text-align:center; margin-top:100px; color:#e74c3c; font-family:sans-serif;'>Invalid Action or Request Failure</h2>";
    }
} else {
    echo "<h2 style='text-align:center; margin-top:100px; color:#e74c3c; font-family:sans-serif;'>Invalid Action or Request Failure</h2>";
}
?>
