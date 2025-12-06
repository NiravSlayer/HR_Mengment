<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class DocumentController
{
    public function getAll(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            
            if ($role === 'HR') {
                $empId = $_GET['empId'] ?? null;
                if ($empId) {
                    $stmt = $pdo->prepare('
                        SELECT * FROM Documents 
                        WHERE Emp_id = :empId 
                        ORDER BY Uploaded_at DESC
                    ');
                    $stmt->execute(['empId' => $empId]);
                } else {
                    $stmt = $pdo->query('
                        SELECT d.*, e.Emp_firstName, e.Emp_lastName, e.Emp_code
                        FROM Documents d
                        JOIN Employees e ON d.Emp_id = e.Emp_id
                        ORDER BY d.Uploaded_at DESC
                    ');
                }
            } else {
                $stmt = $pdo->prepare('
                    SELECT * FROM Documents 
                    WHERE Emp_id = :empId 
                    ORDER BY Uploaded_at DESC
                ');
                $stmt->execute(['empId' => $userId]);
            }
            
            $documents = $stmt->fetchAll();
            Response::json(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function upload(): void
    {
        Auth::requireAuth();
        
        // Handle file upload (multipart/form-data)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            try {
                $file = $_FILES['file'];
                $empId = isset($_POST['empId']) ? (int)$_POST['empId'] : Auth::getUserId();
                $role = Auth::getRole();
                
                // Only HR can upload for other employees
                if ($role !== 'HR' && $empId != Auth::getUserId()) {
                    Response::json(['error' => 'Access denied'], 403);
                }
                
                $uploadDir = __DIR__ . '/../../uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($file['name']);
                $filePath = $uploadDir . $fileName;
                $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    Response::json(['error' => 'File upload failed'], 500);
                }
                
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare('
                    INSERT INTO Documents (Emp_id, File_name, File_type, File_path, Uploaded_by)
                    VALUES (:empId, :fileName, :fileType, :filePath, :uploadedBy)
                ');
                $stmt->execute([
                    'empId' => $empId,
                    'fileName' => $file['name'],
                    'fileType' => $fileType,
                    'filePath' => 'uploads/documents/' . $fileName,
                    'uploadedBy' => Auth::getUserId()
                ]);
                
                Response::json(['success' => true, 'message' => 'Document uploaded', 'id' => $pdo->lastInsertId()]);
            } catch (\Exception $e) {
                Response::json(['error' => $e->getMessage()], 500);
            }
        } else {
            Response::json(['error' => 'No file uploaded'], 400);
        }
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM Documents WHERE Doc_id = :id');
            $stmt->execute(['id' => $id]);
            $doc = $stmt->fetch();
            
            if (!$doc) {
                Response::json(['error' => 'Document not found'], 404);
            }
            
            $role = Auth::getRole();
            if ($role !== 'HR' && $doc['Emp_id'] != Auth::getUserId()) {
                Response::json(['error' => 'Access denied'], 403);
            }
            
            // Delete file
            $filePath = __DIR__ . '/../../' . $doc['File_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete record
            $delStmt = $pdo->prepare('DELETE FROM Documents WHERE Doc_id = :id');
            $delStmt->execute(['id' => $id]);
            
            Response::json(['success' => true, 'message' => 'Document deleted']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }
}

