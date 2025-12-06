<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class TaskController
{
    public function getAll(): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('
                SELECT t.*, 
                    e.Emp_firstName, e.Emp_lastName, e.Emp_code as AssignedToName,
                    h.Hr_code as AssignedByName
                FROM Tasks t
                LEFT JOIN Employees e ON t.Assigned_to = e.Emp_id
                LEFT JOIN HR_Admins h ON t.Assigned_by = h.Hr_id
                ORDER BY t.Created_at DESC
            ');
            $tasks = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $tasks]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMyTasks(): void
    {
        Auth::requireAuth();
        
        $empId = Auth::getUserId();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT t.*, h.Hr_code as AssignedByName
                FROM Tasks t
                LEFT JOIN HR_Admins h ON t.Assigned_by = h.Hr_id
                WHERE t.Assigned_to = :empId
                ORDER BY t.Created_at DESC
            ');
            $stmt->execute(['empId' => $empId]);
            $tasks = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $tasks]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(): void
    {
        Auth::requireHR();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                INSERT INTO Tasks (Title, Description, Assigned_by, Assigned_to, Priority, Status, Due_date)
                VALUES (:title, :description, :assignedBy, :assignedTo, :priority, :status, :dueDate)
            ');
            $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assignedBy' => Auth::getUserId(),
                'assignedTo' => $data['assignedTo'],
                'priority' => $data['priority'] ?? 'Medium',
                'status' => $data['status'] ?? 'Pending',
                'dueDate' => $data['dueDate'] ?? null
            ]);
            
            $taskId = $pdo->lastInsertId();
            
            // Create notification
            $this->createNotification('EMP', $data['assignedTo'], 'New Task Assigned', 
                'You have been assigned a new task: ' . $data['title']);
            
            Response::json(['success' => true, 'message' => 'Task created', 'id' => $taskId]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            
            // Check if user can update (HR or assigned employee)
            $checkStmt = $pdo->prepare('SELECT Assigned_to, Assigned_by FROM Tasks WHERE Task_id = :id');
            $checkStmt->execute(['id' => $id]);
            $task = $checkStmt->fetch();
            
            if (!$task) {
                Response::json(['error' => 'Task not found'], 404);
            }
            
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            
            if ($role !== 'HR' && $task['Assigned_to'] != $userId) {
                Response::json(['error' => 'Access denied'], 403);
            }
            
            $updates = [];
            $params = ['id' => $id];
            
            $fields = ['Title', 'Description', 'Priority', 'Status', 'Due_date'];
            foreach ($fields as $field) {
                $key = lcfirst($field);
                if (isset($data[$key])) {
                    $updates[] = "$field = :$key";
                    $params[$key] = $data[$key];
                }
            }
            
            if (!empty($updates)) {
                $sql = 'UPDATE Tasks SET ' . implode(', ', $updates) . ' WHERE Task_id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Notify HR if employee updated status
                if ($role === 'EMP' && isset($data['status'])) {
                    $this->createNotification('HR', $task['Assigned_by'], 'Task Status Updated', 
                        'Task status has been updated to: ' . $data['status']);
                }
            }
            
            Response::json(['success' => true, 'message' => 'Task updated']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    private function createNotification(string $targetType, int $targetId, string $title, string $message): void
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                INSERT INTO Notifications (Target_type, Target_id, Title, Message, Status)
                VALUES (:type, :id, :title, :message, "Unread")
            ');
            $stmt->execute(['type' => $targetType, 'id' => $targetId, 'title' => $title, 'message' => $message]);
        } catch (\Exception $e) {
            error_log('Notification failed: ' . $e->getMessage());
        }
    }
}

