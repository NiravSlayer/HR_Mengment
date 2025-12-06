<?php
/**
 * Quick script to create/update HR Admin user
 * Run this once: php database/create_admin.php
 * Or access via browser: http://localhost/HR_Menegment/database/create_admin.php
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/models/Database.php';

use HRMS\Models\Database;

echo "<!DOCTYPE html><html><head><title>Create HR Admin</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;}";
echo ".info{color:#004085;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:5px;margin:10px 0;}";
echo "pre{background:#fff;padding:10px;border:1px solid #ddd;border-radius:5px;}</style></head><body>";
echo "<h2>HRMS - Create HR Admin User</h2>";

try {
    $pdo = Database::getConnection();
    
    // Default admin credentials
    $email = 'admin@hrms.local';
    $password = 'Admin@123';
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT Hr_id, Hr_code FROM HR_Admins WHERE Hr_email = :email');
    $stmt->execute(['email' => $email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update password to ensure it's correct
        $updateStmt = $pdo->prepare('
            UPDATE HR_Admins 
            SET Hr_password = :password 
            WHERE Hr_email = :email
        ');
        $updateStmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
        
        echo "<div class='success'>";
        echo "✅ HR Admin password updated successfully!<br>";
        echo "Email: <strong>$email</strong><br>";
        echo "Password: <strong>$password</strong><br>";
        echo "Code: {$existing['Hr_code']}<br>";
        echo "</div>";
    } else {
        // Insert new admin
        $stmt = $pdo->prepare('
            INSERT INTO HR_Admins (Hr_code, Hr_email, Hr_password, Hr_phone, Created_at) 
            VALUES (:code, :email, :password, :phone, NOW())
        ');
        
        $stmt->execute([
            'code' => 'HR001',
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => '1234567890'
        ]);
        
        echo "<div class='success'>";
        echo "✅ HR Admin created successfully!<br>";
        echo "Email: <strong>$email</strong><br>";
        echo "Password: <strong>$password</strong><br>";
        echo "Code: HR001<br>";
        echo "</div>";
    }
    
    // Verify the password works
    echo "<div class='info'>";
    echo "<h3>Verification:</h3>";
    $verifyStmt = $pdo->prepare('SELECT Hr_password FROM HR_Admins WHERE Hr_email = :email');
    $verifyStmt->execute(['email' => $email]);
    $verify = $verifyStmt->fetch();
    
    if ($verify && password_verify($password, $verify['Hr_password'])) {
        echo "✅ Password hash verified successfully!<br>";
        echo "You can now login with:<br>";
        echo "<pre>Email: $email\nPassword: $password</pre>";
    } else {
        echo "❌ Password verification failed. Please try again.<br>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "Make sure:<br>";
    echo "1. Database 'hrms_db' exists<br>";
    echo "2. Database tables are created (run hrms.sql first)<br>";
    echo "3. Database credentials in backend/config/config.php are correct<br>";
    echo "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='../backend/test_login.php'>Test Login Functionality</a> | ";
echo "<a href='../frontend/index.html'>Go to Login Page</a></p>";
echo "</body></html>";
