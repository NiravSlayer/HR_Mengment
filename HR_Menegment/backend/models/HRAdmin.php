<?php

declare(strict_types=1);

namespace HRMS\Models;

use HRMS\Models\Database;
use PDO;
use PDOException;

class HRAdmin
{
    public static function findByEmail(string $email): ?array
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM HR_Admins WHERE Hr_email = :email LIMIT 1');
            $stmt->execute(['email' => trim($email)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            error_log('HRAdmin::findByEmail error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function findById(int $id): ?array
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM HR_Admins WHERE Hr_id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (PDOException $e) {
            error_log('HRAdmin::findById error: ' . $e->getMessage());
            throw $e;
        }
    }
}
