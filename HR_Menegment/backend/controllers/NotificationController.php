<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class NotificationController
{
    public function getMyNotifications(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            
            $targetType = $role === 'HR' ? 'HR' : 'EMP';
            
            $stmt = $pdo->prepare('
                SELECT * FROM Notifications 
                WHERE Target_type = :type AND Target_id = :id
                ORDER BY Created_at DESC
            ');
            $stmt->execute(['type' => $targetType, 'id' => $userId]);
            $notifications = $stmt->fetchAll();
            
            Response::json(['success' => true, 'data' => $notifications]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead(int $id): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            $targetType = $role === 'HR' ? 'HR' : 'EMP';
            
            $stmt = $pdo->prepare('
                UPDATE Notifications 
                SET Status = "Read"
                WHERE Notification_id = :id AND Target_type = :type AND Target_id = :userId
            ');
            $stmt->execute(['id' => $id, 'type' => $targetType, 'userId' => $userId]);
            
            Response::json(['success' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            $targetType = $role === 'HR' ? 'HR' : 'EMP';
            
            $stmt = $pdo->prepare('
                UPDATE Notifications 
                SET Status = "Read"
                WHERE Target_type = :type AND Target_id = :userId AND Status = "Unread"
            ');
            $stmt->execute(['type' => $targetType, 'userId' => $userId]);
            
            Response::json(['success' => true, 'message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUnreadCount(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            $targetType = $role === 'HR' ? 'HR' : 'EMP';
            
            $stmt = $pdo->prepare('
                SELECT COUNT(*) as count FROM Notifications 
                WHERE Target_type = :type AND Target_id = :userId AND Status = "Unread"
            ');
            $stmt->execute(['type' => $targetType, 'id' => $userId]);
            $result = $stmt->fetch();
            
            Response::json(['success' => true, 'count' => (int)$result['count']]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }
}

