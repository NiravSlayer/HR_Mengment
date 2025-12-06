<?php

declare(strict_types=1);

namespace HRMS\Controllers;

use HRMS\Helpers\Response;
use HRMS\Models\HRAdmin;
use HRMS\Models\Employee;
use HRMS\Models\Database;

class AuthController
{
    public function login(): void
    {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
        }

        // Get JSON input
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::json(['error' => 'Invalid JSON data'], 400);
        }

        // Validate input
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = trim($data['role'] ?? '');

        if (empty($email) || empty($password)) {
            Response::json(['error' => 'Email and password are required'], 400);
        }

        if ($role !== 'HR' && $role !== 'EMP') {
            Response::json(['error' => 'Invalid role. Must be HR or EMP'], 400);
        }

        try {
            // Get user based on role
            if ($role === 'HR') {
                $user = HRAdmin::findByEmail($email);
                $idKey = 'Hr_id';
                $passwordKey = 'Hr_password';
                $nameKey = 'Hr_code'; // HR table uses Hr_code, not Hr_name
            } else {
                $user = Employee::findByEmail($email);
                $idKey = 'Emp_id';
                $passwordKey = 'Emp_password';
                $nameKey = 'Emp_firstName';
            }

            // Check if user exists
            if (!$user) {
                Response::json(['error' => 'Incorrect email or password'], 401);
            }

            // Verify password
            $storedHash = $user[$passwordKey] ?? '';
            if (empty($storedHash)) {
                Response::json(['error' => 'Invalid user account'], 401);
            }

            if (!password_verify($password, $storedHash)) {
                Response::json(['error' => 'Incorrect email or password'], 401);
            }

            // Set session
            $_SESSION['user_id'] = (int)$user[$idKey];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $user[$nameKey] ?? 'User';

            Response::json([
                'success' => true,
                'message' => 'Login successful',
                'role' => $role,
                'user_id' => $_SESSION['user_id']
            ]);

        } catch (\PDOException $e) {
            error_log('Database error in login: ' . $e->getMessage());
            Response::json(['error' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            Response::json(['error' => 'Login failed. Please try again.'], 500);
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        Response::json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function checkSession(): void
    {
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            Response::json([
                'authenticated' => true,
                'role' => $_SESSION['role'],
                'user_id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'] ?? '',
                'name' => $_SESSION['name'] ?? ''
            ]);
        } else {
            Response::json(['authenticated' => false], 401);
        }
    }

    public function autoLogin(): void
    {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
        }

        // Get JSON input
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $role = trim($data['role'] ?? '');

        if ($role !== 'HR' && $role !== 'EMP') {
            Response::json(['error' => 'Invalid role. Must be HR or EMP'], 400);
        }

        try {
            $pdo = Database::getConnection();

            if ($role === 'HR') {
                // Get first HR admin from database
                $stmt = $pdo->query('SELECT * FROM HR_Admins ORDER BY Hr_id LIMIT 1');
                $user = $stmt->fetch();
                
                if (!$user) {
                    Response::json(['error' => 'No HR admin found in database'], 404);
                }

                $_SESSION['user_id'] = (int)$user['Hr_id'];
                $_SESSION['role'] = 'HR';
                $_SESSION['email'] = $user['Hr_email'];
                $_SESSION['name'] = $user['Hr_code'] ?? 'HR Admin';
            } else {
                // Get first employee from database
                $stmt = $pdo->query('SELECT * FROM Employees ORDER BY Emp_id LIMIT 1');
                $user = $stmt->fetch();
                
                if (!$user) {
                    Response::json(['error' => 'No employee found in database'], 404);
                }

                $_SESSION['user_id'] = (int)$user['Emp_id'];
                $_SESSION['role'] = 'EMP';
                $_SESSION['email'] = $user['Emp_email'];
                $_SESSION['name'] = ($user['Emp_firstName'] ?? '') . ' ' . ($user['Emp_lastName'] ?? '');
            }

            Response::json([
                'success' => true,
                'message' => 'Auto-login successful',
                'role' => $role,
                'user_id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'name' => $_SESSION['name']
            ]);

        } catch (\PDOException $e) {
            error_log('Database error in auto-login: ' . $e->getMessage());
            Response::json(['error' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            error_log('Auto-login error: ' . $e->getMessage());
            Response::json(['error' => 'Auto-login failed. Please try again.'], 500);
        }
    }
}
