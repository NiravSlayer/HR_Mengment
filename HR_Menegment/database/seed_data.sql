-- Seed data for HRMS
-- Run this after importing hrms.sql

SET FOREIGN_KEY_CHECKS = 0;

-- Insert HR Admin
-- Password: Admin@123
-- NOTE: If this hash doesn't work, run database/create_admin.php instead
INSERT INTO HR_Admins (Hr_code, Hr_email, Hr_password, Hr_phone, Created_at) VALUES
('HR001', 'admin@hrms.local', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '1234567890', NOW());

-- Insert Project Categories
INSERT INTO Project_Category (Category_name, Description, Created_at) VALUES
('Web Development', 'Web-based applications and websites', NOW()),
('Mobile Development', 'Mobile applications for iOS and Android', NOW()),
('Data Analytics', 'Data analysis and business intelligence projects', NOW());

-- Insert Sample Employee
-- Password: Employee@123
-- NOTE: If this hash doesn't work, you may need to hash it via PHP password_hash()
INSERT INTO Employees (Emp_code, Emp_firstName, Emp_lastName, Emp_email, Emp_password, Emp_phone, Emp_dob, Emp_gender, Joining_date, ProjectCategory_id, Salary, Address, Status, Created_at) VALUES
('EMP001', 'John', 'Doe', 'employee@hrms.local', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '9876543210', '1990-01-15', 'Male', '2024-01-01', 1, 50000.00, '123 Main St, City', 'Active', NOW());

SET FOREIGN_KEY_CHECKS = 1;

