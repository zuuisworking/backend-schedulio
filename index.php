<?php
// filename: index.php
require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\AuthController;
use App\TaskController;
use App\SchedulioController;
use App\AuthMiddleware;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$requestData = json_decode(file_get_contents("php://input"), true);

if ($uri === '/api/register' && $method === 'POST') {
    $auth = new AuthController($db);
    $response = $auth->register($requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}  elseif ($uri === '/api/login' && $method === 'POST') {
    $auth = new AuthController($db);
    $response = $auth->login($requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
} elseif ($uri === '/api/tasks' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    $taskController = new TaskController($db);
    $response = $taskController->getTasks($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
} elseif ($uri === '/api/tasks' && $method === 'POST') {
    $userData = AuthMiddleware::checkToken();  
    $taskController = new TaskController($db);
    $response = $taskController->createTask($userData->id, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
} elseif (preg_match('/^\/api\/tasks\/(\d+)$/', $uri, $matches) && $method === 'PUT') {
    $userData = AuthMiddleware::checkToken();
    $taskId = $matches[1];  
    $taskController = new TaskController($db);
    $response = $taskController->updateTask($userData->id, $taskId, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
} elseif (preg_match('/^\/api\/tasks\/(\d+)$/', $uri, $matches) && $method === 'DELETE') {
    $userData = AuthMiddleware::checkToken();
    $taskId = $matches[1];  
    $taskController = new TaskController($db);
    $response = $taskController->deleteTask($userData->id, $taskId);
    
    http_response_code($response['status']);
    echo json_encode($response);
} elseif ($uri === '/api/schedulio/calculate' && $method === 'POST') {
    $userData = AuthMiddleware::checkToken();
    $schedulioController = new SchedulioController($db);
    $response = $schedulioController->calculate($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/schedulio/recommendations' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    $schedulioController = new SchedulioController($db);
    $response = $schedulioController->getRecommendations($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['status' => 404, 'message' => 'Endpoint tidak ditemukan.']);
}