<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\AuthController;
use App\TaskController;
use App\ActivityController;
use App\CourseController;
use App\DashboardController;
use App\SchedulioController;
use App\AuthMiddleware;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

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
} 
elseif ($uri === '/api/login' && $method === 'POST') {
    $auth = new AuthController($db);
    $response = $auth->login($requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
} 
elseif ($uri === '/api/tasks' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    
    $taskController = new TaskController($db);
    $response = $taskController->getTasks($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/tasks' && $method === 'POST') {
    $userData = AuthMiddleware::checkToken();
    
    $taskController = new TaskController($db);
    $response = $taskController->createTask($userData->id, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/tasks\/(\d+)$/', $uri, $matches) && $method === 'PUT') {
    $userData = AuthMiddleware::checkToken();
    $taskId = $matches[1];
    
    $taskController = new TaskController($db);
    $response = $taskController->updateTask($userData->id, $taskId, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/tasks\/(\d+)$/', $uri, $matches) && $method === 'DELETE') {
    $userData = AuthMiddleware::checkToken();
    $taskId = $matches[1];
    
    $taskController = new TaskController($db);
    $response = $taskController->deleteTask($userData->id, $taskId);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/activities' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    
    $activityController = new ActivityController($db);
    $response = $activityController->getActivities($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/schedules' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    
    $activityController = new ActivityController($db);
    $response = $activityController->getActivities($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/activities' && $method === 'POST') {
    $userData = AuthMiddleware::checkToken();
    
    $activityController = new ActivityController($db);
    $response = $activityController->createActivity($userData->id, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/activities\/(\d+)$/', $uri, $matches) && $method === 'PUT') {
    $userData = AuthMiddleware::checkToken();
    $activityId = $matches[1];
    
    $activityController = new ActivityController($db);
    $response = $activityController->updateActivity($userData->id, $activityId, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/activities\/(\d+)$/', $uri, $matches) && $method === 'DELETE') {
    $userData = AuthMiddleware::checkToken();
    $activityId = $matches[1];
    
    $activityController = new ActivityController($db);
    $response = $activityController->deleteActivity($userData->id, $activityId);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/dashboard/nearest' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    
    $dashboardController = new DashboardController($db);
    $response = $dashboardController->getNearestSchedules($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/courses' && $method === 'GET') {
    $userData = AuthMiddleware::checkToken();
    
    $courseController = new CourseController($db);
    $response = $courseController->getCourses($userData->id);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/courses' && $method === 'POST') {
    $userData = AuthMiddleware::checkToken();
    
    $courseController = new CourseController($db);
    $response = $courseController->createCourse($userData->id, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/courses\/(\d+)$/', $uri, $matches) && $method === 'PUT') {
    $userData = AuthMiddleware::checkToken();
    $courseId = $matches[1];
    
    $courseController = new CourseController($db);
    $response = $courseController->updateCourse($userData->id, $courseId, $requestData);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif (preg_match('/^\/api\/courses\/(\d+)$/', $uri, $matches) && $method === 'DELETE') {
    $userData = AuthMiddleware::checkToken();
    $courseId = $matches[1];
    
    $courseController = new CourseController($db);
    $response = $courseController->deleteCourse($userData->id, $courseId);
    
    http_response_code($response['status']);
    echo json_encode($response);
}
elseif ($uri === '/api/schedulio/calculate' && $method === 'POST') {
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
}
else {
    http_response_code(404);
    echo json_encode(['status' => 404, 'message' => 'Endpoint tidak ditemukan.']);
}