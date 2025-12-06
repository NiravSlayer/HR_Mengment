<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Middleware\Auth;
use HRMS\Models\Database;

class DashboardController
{
    public function getStats(): void
    {
        Auth::requireAuth();
        
        try {
            $pdo = Database::getConnection();
            $role = Auth::getRole();
            $userId = Auth::getUserId();
            
            if ($role === 'HR') {
                // Admin stats
                $stats = [
                    'totalEmployees' => $this->getCount('Employees', 'Status = "Active"'),
                    'totalProjects' => $this->getCount('Projects', 'Status = "Active"'),
                    'pendingLeaves' => $this->getCount('Leaves', 'Status = "Pending"'),
                    'pendingTasks' => $this->getCount('Tasks', 'Status = "Pending"'),
                    'todayAttendance' => $this->getCount('Attendance', 'Date = CURDATE() AND Status = "Present"'),
                ];
            } else {
                // Employee stats
                $stats = [
                    'myTasks' => $this->getCount('Tasks', 'Assigned_to = ' . $userId),
                    'pendingTasks' => $this->getCount('Tasks', 'Assigned_to = ' . $userId . ' AND Status = "Pending"'),
                    'myProjects' => $this->getCount('Project_Assign', 'Emp_id = ' . $userId),
                    'myLeaves' => $this->getCount('Leaves', 'Emp_id = ' . $userId),
                    'pendingLeaves' => $this->getCount('Leaves', 'Emp_id = ' . $userId . ' AND Status = "Pending"'),
                ];
            }
            
            Response::json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    private function getCount(string $table, string $condition): int
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table WHERE $condition");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}

