<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class ProjectCategoryController
{
    public function getAll(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('SELECT * FROM Project_Category ORDER BY Category_name');
            $categories = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $categories]);
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
                INSERT INTO Project_Category (Category_name, Description)
                VALUES (:name, :description)
            ');
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);
            
            Response::json(['success' => true, 'message' => 'Category created', 'id' => $pdo->lastInsertId()]);
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
            $stmt = $pdo->prepare('
                UPDATE Project_Category 
                SET Category_name = :name, Description = :description
                WHERE ProjectCategory_id = :id
            ');
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);
            
            Response::json(['success' => true, 'message' => 'Category updated']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(int $id): void
    {
        Auth::requireHR();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('DELETE FROM Project_Category WHERE ProjectCategory_id = :id');
            $stmt->execute(['id' => $id]);
            
            Response::json(['success' => true, 'message' => 'Category deleted']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }
}

