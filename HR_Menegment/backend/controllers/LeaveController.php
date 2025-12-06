<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class LeaveController
{
    public function getAll(): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('
                SELECT l.*, e.Emp_firstName, e.Emp_lastName, e.Emp_code, h.Hr_code as ActionBy
                FROM Leaves l
                JOIN Employees e ON l.Emp_id = e.Emp_id
                LEFT JOIN HR_Admins h ON l.Action_by = h.Hr_id
                ORDER BY l.Requested_at DESC
            ');
            $leaves = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $leaves]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMyLeaves(): void
    {
        Auth::requireAuth();
        
        $empId = Auth::getUserId();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT * FROM Leaves 
                WHERE Emp_id = :empId 
                ORDER BY Requested_at DESC
            ');
            $stmt->execute(['empId' => $empId]);
            $leaves = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $leaves]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function request(): void
    {
        Auth::requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            
            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);
            $days = $startDate->diff($endDate)->days + 1;
            
            $stmt = $pdo->prepare('
                INSERT INTO Leaves (Emp_id, Leave_type, Start_date, End_date, Days, Status, Attachment)
                VALUES (:empId, :type, :start, :end, :days, "Pending", :attachment)
            ');
            
            $stmt->execute([
                'empId' => Auth::getUserId(),
                'type' => $data['type'],
                'start' => $data['startDate'],
                'end' => $data['endDate'],
                'days' => $days,
                'attachment' => $data['attachment'] ?? null
            ]);
            
            $leaveId = $pdo->lastInsertId();
            
            // Create notification
            $this->createNotification('EMP', Auth::getUserId(), 'Leave Request Submitted', 
                'Your leave request has been submitted and is pending approval.');
            
            Response::json(['success' => true, 'message' => 'Leave request submitted', 'id' => $leaveId]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve(int $id): void
    {
        Auth::requireHR();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                UPDATE Leaves 
                SET Status = "Approved", Comment = :comment, Action_by = :actionBy
                WHERE Leave_id = :id
            ');
            $stmt->execute([
                'id' => $id,
                'comment' => $data['comment'] ?? null,
                'actionBy' => Auth::getUserId()
            ]);
            
            // Get employee ID for notification
            $empStmt = $pdo->prepare('SELECT Emp_id FROM Leaves WHERE Leave_id = :id');
            $empStmt->execute(['id' => $id]);
            $leave = $empStmt->fetch();
            
            if ($leave) {
                $this->createNotification('EMP', $leave['Emp_id'], 'Leave Approved', 
                    'Your leave request has been approved.');
            }
            
            Response::json(['success' => true, 'message' => 'Leave approved']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function reject(int $id): void
    {
        Auth::requireHR();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                UPDATE Leaves 
                SET Status = "Rejected", Comment = :comment, Action_by = :actionBy
                WHERE Leave_id = :id
            ');
            $stmt->execute([
                'id' => $id,
                'comment' => $data['comment'] ?? null,
                'actionBy' => Auth::getUserId()
            ]);
            
            // Get employee ID for notification
            $empStmt = $pdo->prepare('SELECT Emp_id FROM Leaves WHERE Leave_id = :id');
            $empStmt->execute(['id' => $id]);
            $leave = $empStmt->fetch();
            
            if ($leave) {
                $this->createNotification('EMP', $leave['Emp_id'], 'Leave Rejected', 
                    'Your leave request has been rejected.');
            }
            
            Response::json(['success' => true, 'message' => 'Leave rejected']);
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
            $stmt->execute([
                'type' => $targetType,
                'id' => $targetId,
                'title' => $title,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            error_log('Notification creation failed: ' . $e->getMessage());
        }
    }
}

