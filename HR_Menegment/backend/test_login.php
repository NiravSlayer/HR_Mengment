<?php
/**
 * Test script to verify login functionality
 * Run: php backend/test_login.php
 * Or access: http://localhost/HR_Menegment/backend/test_login.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/HRAdmin.php';
require_once __DIR__ . '/models/Employee.php';

use HRMS\Models\Database;
use HRMS\Models\HRAdmin;
use HRMS\Models\Employee;

echo "<h2>HRMS Login Test</h2>";

try {
    // Test database connection
    echo "<h3>1. Testing Database Connection...</h3>";
    $pdo = Database::getConnection();
    echo "✅ Database connection successful!<br><br>";

    // Check HR_Admins table
    echo "<h3>2. Checking HR_Admins Table...</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM HR_Admins");
    $hrCount = $stmt->fetch()['count'];
    echo "HR Admins in database: <strong>$hrCount</strong><br>";

    if ($hrCount > 0) {
        $stmt = $pdo->query("SELECT Hr_id, Hr_code, Hr_email, Hr_phone FROM HR_Admins LIMIT 5");
        $hrs = $stmt->fetchAll();
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Code</th><th>Email</th><th>Phone</th></tr>";
        foreach ($hrs as $hr) {
            echo "<tr>";
            echo "<td>{$hr['Hr_id']}</td>";
            echo "<td>{$hr['Hr_code']}</td>";
            echo "<td>{$hr['Hr_email']}</td>";
            echo "<td>{$hr['Hr_phone']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "⚠️ No HR Admins found. Create one using database/create_admin.php<br><br>";
    }

    // Test HR login
    echo "<h3>3. Testing HR Login...</h3>";
    $testEmail = 'admin@hrms.local';
    $testPassword = 'Admin@123';
    
    $hr = HRAdmin::findByEmail($testEmail);
    if ($hr) {
        echo "✅ HR Admin found: {$hr['Hr_email']}<br>";
        echo "Password hash stored: " . substr($hr['Hr_password'], 0, 20) . "...<br>";
        
        if (password_verify($testPassword, $hr['Hr_password'])) {
            echo "✅ Password verification: <strong>SUCCESS</strong><br>";
        } else {
            echo "❌ Password verification: <strong>FAILED</strong><br>";
            echo "The stored password hash does not match 'Admin@123'<br>";
            echo "Run: php database/create_admin.php to create/update admin with correct password<br>";
        }
    } else {
        echo "❌ HR Admin not found with email: $testEmail<br>";
        echo "Run: php database/create_admin.php to create admin user<br>";
    }

    echo "<br>";

    // Check Employees table
    echo "<h3>4. Checking Employees Table...</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Employees");
    $empCount = $stmt->fetch()['count'];
    echo "Employees in database: <strong>$empCount</strong><br><br>";

    // Test Employee login
    if ($empCount > 0) {
        echo "<h3>5. Testing Employee Login...</h3>";
        $testEmpEmail = 'employee@hrms.local';
        $testEmpPassword = 'Employee@123';
        
        $emp = Employee::findByEmail($testEmpEmail);
        if ($emp) {
            echo "✅ Employee found: {$emp['Emp_email']}<br>";
            if (password_verify($testEmpPassword, $emp['Emp_password'])) {
                echo "✅ Password verification: <strong>SUCCESS</strong><br>";
            } else {
                echo "❌ Password verification: <strong>FAILED</strong><br>";
            }
        } else {
            echo "❌ Employee not found with email: $testEmpEmail<br>";
        }
    }

    echo "<br><hr>";
    echo "<h3>Summary</h3>";
    echo "If password verification failed, the password hash in database is incorrect.<br>";
    echo "Solution: Run <code>php database/create_admin.php</code> to create admin with correct password hash.<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

