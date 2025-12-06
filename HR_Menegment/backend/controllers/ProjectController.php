<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class ProjectController
{
    public function getAll(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            
            if ($role === 'HR') {
                $stmt = $pdo->query('
                    SELECT p.*, pc.Category_name
                    FROM Projects p
                    LEFT JOIN Project_Category pc ON p.ProjectCategory_id = pc.ProjectCategory_id
                    ORDER BY p.Created_at DESC
                ');
            } else {
                $empId = Auth::getUserId();
                $stmt = $pdo->prepare('
                    SELECT p.*, pc.Category_name
                    FROM Projects p
                    JOIN Project_Assign pa ON p.Project_id = pa.Project_id
                    LEFT JOIN Project_Category pc ON p.ProjectCategory_id = pc.ProjectCategory_id
                    WHERE pa.Emp_id = :empId
                    ORDER BY p.Created_at DESC
                ');
                $stmt->execute(['empId' => $empId]);
            }
            
            $projects = $stmt->fetchAll();
            
            // Get assigned employees for each project
            foreach ($projects as &$project) {
                $empStmt = $pdo->prepare('
                    SELECT e.Emp_id, e.Emp_firstName, e.Emp_lastName, e.Emp_code
                    FROM Project_Assign pa
                    JOIN Employees e ON pa.Emp_id = e.Emp_id
                    WHERE pa.Project_id = :projectId
                ');
                $empStmt->execute(['projectId' => $project['Project_id']]);
                $project['employees'] = $empStmt->fetchAll();
            }
            
            Response::json(['success' => true, 'data' => $projects]);
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
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare('
                INSERT INTO Projects (Project_name, ProjectCategory_id, Description, Start_date, End_date, Status)
                VALUES (:name, :categoryId, :description, :startDate, :endDate, :status)
            ');
            $stmt->execute([
                'name' => $data['name'],
                'categoryId' => $data['categoryId'] ?? null,
                'description' => $data['description'] ?? null,
                'startDate' => $data['startDate'] ?? null,
                'endDate' => $data['endDate'] ?? null,
                'status' => $data['status'] ?? 'Active'
            ]);
            
            $projectId = $pdo->lastInsertId();
            
            // Assign employees
            if (!empty($data['employeeIds'])) {
                $assignStmt = $pdo->prepare('INSERT INTO Project_Assign (Project_id, Emp_id) VALUES (:projectId, :empId)');
                foreach ($data['employeeIds'] as $empId) {
                    $assignStmt->execute(['projectId' => $projectId, 'empId' => $empId]);
                    
                    // Create notification
                    $this->createNotification('EMP', $empId, 'Project Assigned', 
                        'You have been assigned to project: ' . $data['name']);
                }
            }
            
            $pdo->commit();
            Response::json(['success' => true, 'message' => 'Project created', 'id' => $projectId]);
        } catch (\Exception $e) {
            $pdo->rollBack();
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): void
    {
        Auth::requireHR();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            $updates = [];
            $params = ['id' => $id];
            
            $fields = ['Project_name', 'ProjectCategory_id', 'Description', 'Start_date', 'End_date', 'Status'];
            foreach ($fields as $field) {
                $key = lcfirst(str_replace('Project_', '', $field));
                if (isset($data[$key])) {
                    $updates[] = "$field = :$key";
                    $params[$key] = $data[$key];
                }
            }
            
            if (!empty($updates)) {
                $sql = 'UPDATE Projects SET ' . implode(', ', $updates) . ' WHERE Project_id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            Response::json(['success' => true, 'message' => 'Project updated']);
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

