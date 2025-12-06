<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class AttendanceController
{
    public function getAll(): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $empId = $_GET['empId'] ?? null;
            $categoryId = $_GET['categoryId'] ?? null;
            $date = $_GET['date'] ?? null;
            
            $sql = '
                SELECT a.*, e.Emp_firstName, e.Emp_lastName, e.Emp_code, pc.Category_name
                FROM Attendance a
                JOIN Employees e ON a.Emp_id = e.Emp_id
                LEFT JOIN Project_Category pc ON e.ProjectCategory_id = pc.ProjectCategory_id
                WHERE 1=1
            ';
            $params = [];
            
            if ($empId) {
                $sql .= ' AND a.Emp_id = :empId';
                $params['empId'] = $empId;
            }
            if ($categoryId) {
                $sql .= ' AND e.ProjectCategory_id = :categoryId';
                $params['categoryId'] = $categoryId;
            }
            if ($date) {
                $sql .= ' AND a.Date = :date';
                $params['date'] = $date;
            }
            
            $sql .= ' ORDER BY a.Date DESC, a.Attendance_id DESC';
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $attendance = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $attendance]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMyAttendance(): void
    {
        Auth::requireAuth();
        
        $empId = Auth::getUserId();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT * FROM Attendance 
                WHERE Emp_id = :empId 
                ORDER BY Date DESC
            ');
            $stmt->execute(['empId' => $empId]);
            $attendance = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $attendance]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function mark(): void
    {
        Auth::requireHR();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $pdo = Database::getConnection();
            
            // Check if already marked
            $checkStmt = $pdo->prepare('SELECT Attendance_id FROM Attendance WHERE Emp_id = :empId AND Date = :date');
            $checkStmt->execute(['empId' => $data['empId'], 'date' => $data['date']]);
            
            if ($checkStmt->fetch()) {
                // Update existing
                $stmt = $pdo->prepare('
                    UPDATE Attendance 
                    SET Status = :status, Checkin_time = :checkin, Checkout_time = :checkout, Marked_by = :markedBy
                    WHERE Emp_id = :empId AND Date = :date
                ');
            } else {
                // Insert new
                $stmt = $pdo->prepare('
                    INSERT INTO Attendance (Emp_id, Date, Status, Checkin_time, Checkout_time, Marked_by)
                    VALUES (:empId, :date, :status, :checkin, :checkout, :markedBy)
                ');
            }
            
            $stmt->execute([
                'empId' => $data['empId'],
                'date' => $data['date'],
                'status' => $data['status'],
                'checkin' => $data['checkinTime'] ?? null,
                'checkout' => $data['checkoutTime'] ?? null,
                'markedBy' => Auth::getUserId()
            ]);
            
            Response::json(['success' => true, 'message' => 'Attendance marked successfully']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }
}

