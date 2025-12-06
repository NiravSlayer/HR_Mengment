<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class EmployeeController
{
    public function getAll(): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('
                SELECT e.*, pc.Category_name 
                FROM Employees e 
                LEFT JOIN Project_Category pc ON e.ProjectCategory_id = pc.ProjectCategory_id 
                ORDER BY e.Emp_id DESC
            ');
            $employees = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $employees]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function getById(int $id): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT e.*, pc.Category_name 
                FROM Employees e 
                LEFT JOIN Project_Category pc ON e.ProjectCategory_id = pc.ProjectCategory_id 
                WHERE e.Emp_id = :id
            ');
            $stmt->execute(['id' => $id]);
            $employee = $stmt->fetch();
            
            if (!$employee) {
                Response::json(['error' => 'Employee not found'], 404);
            }
            
            Response::json(['success' => true, 'data' => $employee]);
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
                INSERT INTO Employees (
                    Emp_code, Emp_firstName, Emp_lastName, Emp_email, Emp_password,
                    Emp_phone, Emp_dob, Emp_gender, Joining_date, ProjectCategory_id,
                    Salary, Address, Status
                ) VALUES (
                    :code, :firstName, :lastName, :email, :password,
                    :phone, :dob, :gender, :joiningDate, :categoryId,
                    :salary, :address, :status
                )
            ');
            
            $password = password_hash($data['password'] ?? 'Employee@123', PASSWORD_BCRYPT);
            
            $stmt->execute([
                'code' => $data['code'],
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'email' => $data['email'],
                'password' => $password,
                'phone' => $data['phone'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'] ?? null,
                'joiningDate' => $data['joiningDate'] ?? date('Y-m-d'),
                'categoryId' => $data['categoryId'] ?? null,
                'salary' => $data['salary'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? 'Active'
            ]);
            
            Response::json(['success' => true, 'message' => 'Employee created successfully', 'id' => $pdo->lastInsertId()]);
        } catch (\Exception $e) {
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
            
            $fields = ['Emp_code', 'Emp_firstName', 'Emp_lastName', 'Emp_email', 'Emp_phone', 
                      'Emp_dob', 'Emp_gender', 'Joining_date', 'ProjectCategory_id', 'Salary', 'Address', 'Status'];
            
            foreach ($fields as $field) {
                $key = lcfirst(str_replace('Emp_', '', $field));
                $key = str_replace('_', '', $key);
                if (isset($data[$key])) {
                    $updates[] = "$field = :$key";
                    $params[$key] = $data[$key];
                }
            }
            
            if (empty($updates)) {
                Response::json(['error' => 'No fields to update'], 400);
            }
            
            $sql = 'UPDATE Employees SET ' . implode(', ', $updates) . ' WHERE Emp_id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            Response::json(['success' => true, 'message' => 'Employee updated successfully']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(int $id): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('DELETE FROM Employees WHERE Emp_id = :id');
            $stmt->execute(['id' => $id]);
            
            Response::json(['success' => true, 'message' => 'Employee deleted successfully']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }
}

