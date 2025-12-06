<?php
// HRMS Backend Entry Point

declare(strict_types=1);

// Start session first
session_start();

// Autoload classes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/HRAdmin.php';
require_once __DIR__ . '/models/Employee.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/EmployeeController.php';
require_once __DIR__ . '/controllers/AttendanceController.php';
require_once __DIR__ . '/controllers/LeaveController.php';
require_once __DIR__ . '/controllers/ProjectCategoryController.php';
require_once __DIR__ . '/controllers/ProjectController.php';
require_once __DIR__ . '/controllers/TaskController.php';
require_once __DIR__ . '/controllers/DocumentController.php';
require_once __DIR__ . '/controllers/NotificationController.php';
require_once __DIR__ . '/controllers/DashboardController.php';

use HRMS\Helpers\Response;
use HRMS\Controllers\AuthController;
use HRMS\Controllers\EmployeeController;
use HRMS\Controllers\AttendanceController;
use HRMS\Controllers\LeaveController;
use HRMS\Controllers\ProjectCategoryController;
use HRMS\Controllers\ProjectController;
use HRMS\Controllers\TaskController;
use HRMS\Controllers\DocumentController;
use HRMS\Controllers\NotificationController;
use HRMS\Controllers\DashboardController;

// CORS headers (for development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get action and resource from query string
$action = $_GET['action'] ?? 'health';
$resource = $_GET['resource'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $authController = new AuthController();

    switch ($action) {
        case 'health':
            Response::json([
                'status' => 'ok',
                'message' => 'HRMS backend is running',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'login':
            $authController->login();
            break;

        case 'logout':
            $authController->logout();
            break;

        case 'check-session':
            $authController->checkSession();
            break;

        case 'auto-login':
            $authController->autoLogin();
            break;

        case 'dashboard':
            (new DashboardController())->getStats();
            break;

        // Employee Management
        case 'employees':
            $controller = new EmployeeController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($id > 0) {
                    $controller->getById($id);
                } else {
                    $controller->getAll();
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller->update($id);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller->delete($id);
            }
            break;

        // Attendance
        case 'attendance':
            $controller = new AttendanceController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($resource === 'my') {
                    $controller->getMyAttendance();
                } else {
                    $controller->getAll();
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->mark();
            }
            break;

        // Leaves
        case 'leaves':
            $controller = new LeaveController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($resource === 'my') {
                    $controller->getMyLeaves();
                } else {
                    $controller->getAll();
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->request();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                if ($resource === 'approve') {
                    $controller->approve($id);
                } elseif ($resource === 'reject') {
                    $controller->reject($id);
                }
            }
            break;

        // Project Categories
        case 'project-categories':
            $controller = new ProjectCategoryController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getAll();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller->update($id);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller->delete($id);
            }
            break;

        // Projects
        case 'projects':
            $controller = new ProjectController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getAll();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller->update($id);
            }
            break;

        // Tasks
        case 'tasks':
            $controller = new TaskController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($resource === 'my') {
                    $controller->getMyTasks();
                } else {
                    $controller->getAll();
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller->update($id);
            }
            break;

        // Documents
        case 'documents':
            // For file uploads, don't set JSON header
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
                header('Content-Type: application/json');
            }
            $controller = new DocumentController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getAll();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->upload();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller->delete($id);
            }
            break;

        // Notifications
        case 'notifications':
            $controller = new NotificationController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($resource === 'count') {
                    $controller->getUnreadCount();
                } else {
                    $controller->getMyNotifications();
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                if ($id > 0) {
                    $controller->markAsRead($id);
                } elseif ($resource === 'read-all') {
                    $controller->markAllAsRead();
                }
            }
            break;

        case 'test-db':
            try {
                $pdo = \HRMS\Models\Database::getConnection();
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM HR_Admins');
                $result = $stmt->fetch();
                Response::json([
                    'success' => true,
                    'message' => 'Database connection successful',
                    'hr_admins_count' => $result['count'] ?? 0
                ]);
            } catch (\Exception $e) {
                Response::json([
                    'success' => false,
                    'error' => 'Database connection failed',
                    'message' => $e->getMessage()
                ], 500);
            }
            break;

        default:
            Response::json(['error' => 'Unknown action: ' . $action], 404);
    }
} catch (\Throwable $e) {
    error_log('Backend error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    Response::json([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
