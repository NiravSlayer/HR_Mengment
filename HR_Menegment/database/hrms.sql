SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Notifications;
DROP TABLE IF EXISTS Documents;
DROP TABLE IF EXISTS Leaves;
DROP TABLE IF EXISTS Attendance;
DROP TABLE IF EXISTS Tasks;
DROP TABLE IF EXISTS Project_Assign;
DROP TABLE IF EXISTS Projects;
DROP TABLE IF EXISTS Project_Category;
DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS HR_Admins;

CREATE TABLE HR_Admins (
    Hr_id INT AUTO_INCREMENT PRIMARY KEY,
    Hr_code VARCHAR(150) NOT NULL,
    Hr_email VARCHAR(255) NOT NULL UNIQUE,
    Hr_password VARCHAR(255) NOT NULL,
    Hr_phone VARCHAR(20),
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Project_Category (
    ProjectCategory_id INT AUTO_INCREMENT PRIMARY KEY,
    Category_name VARCHAR(150) NOT NULL,
    Description TEXT,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Employees (
    Emp_id INT AUTO_INCREMENT PRIMARY KEY,
    Emp_code VARCHAR(50) NOT NULL UNIQUE,
    Emp_firstName VARCHAR(100) NOT NULL,
    Emp_lastName VARCHAR(100) NOT NULL,
    Emp_email VARCHAR(255) NOT NULL UNIQUE,
    Emp_password VARCHAR(255) NOT NULL,
    Emp_phone VARCHAR(50),
    Emp_dob DATE,
    Emp_gender VARCHAR(10),
    Joining_date DATE,
    ProjectCategory_id INT,
    Salary DECIMAL(12, 2),
    Address TEXT,
    Profile_pic VARCHAR(255),
    Status VARCHAR(20) DEFAULT 'Active',
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_project_category
        FOREIGN KEY (ProjectCategory_id) REFERENCES Project_Category(ProjectCategory_id)
);

CREATE TABLE Projects (
    Project_id INT AUTO_INCREMENT PRIMARY KEY,
    Project_name VARCHAR(200) NOT NULL,
    ProjectCategory_id INT,
    Description TEXT,
    Start_date DATE,
    End_date DATE,
    Status VARCHAR(50) DEFAULT 'Active',
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_project_category
        FOREIGN KEY (ProjectCategory_id) REFERENCES Project_Category(ProjectCategory_id)
);

CREATE TABLE Project_Assign (
    Assign_id INT AUTO_INCREMENT PRIMARY KEY,
    Project_id INT NOT NULL,
    Emp_id INT NOT NULL,
    Assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assign_project
        FOREIGN KEY (Project_id) REFERENCES Projects(Project_id),
    CONSTRAINT fk_assign_employee
        FOREIGN KEY (Emp_id) REFERENCES Employees(Emp_id)
);

CREATE TABLE Tasks (
    Task_id INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    Assigned_by INT,
    Assigned_to INT,
    Priority VARCHAR(50),
    Status VARCHAR(50) DEFAULT 'Pending',
    Due_date DATE,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_assigned_by
        FOREIGN KEY (Assigned_by) REFERENCES HR_Admins(Hr_id),
    CONSTRAINT fk_tasks_assigned_to
        FOREIGN KEY (Assigned_to) REFERENCES Employees(Emp_id)
);

CREATE TABLE Attendance (
    Attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    Emp_id INT NOT NULL,
    Date DATE NOT NULL,
    Status VARCHAR(20) NOT NULL,
    Checkin_time TIME,
    Checkout_time TIME,
    Marked_by INT,
    CONSTRAINT fk_attendance_employee
        FOREIGN KEY (Emp_id) REFERENCES Employees(Emp_id),
    CONSTRAINT fk_attendance_marked_by
        FOREIGN KEY (Marked_by) REFERENCES HR_Admins(Hr_id)
);

CREATE TABLE Leaves (
    Leave_id INT AUTO_INCREMENT PRIMARY KEY,
    Emp_id INT NOT NULL,
    Leave_type VARCHAR(100),
    Start_date DATE,
    End_date DATE,
    Days INT,
    Status VARCHAR(20) DEFAULT 'Pending',
    Comment TEXT,
    Attachment VARCHAR(255),
    Requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    Action_by INT,
    CONSTRAINT fk_leaves_employee
        FOREIGN KEY (Emp_id) REFERENCES Employees(Emp_id),
    CONSTRAINT fk_leaves_action_by
        FOREIGN KEY (Action_by) REFERENCES HR_Admins(Hr_id)
);

CREATE TABLE Documents (
    Doc_id INT AUTO_INCREMENT PRIMARY KEY,
    Emp_id INT,
    File_name VARCHAR(255) NOT NULL,
    File_type VARCHAR(100),
    File_path VARCHAR(255) NOT NULL,
    Uploaded_by INT,
    Uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_employee
        FOREIGN KEY (Emp_id) REFERENCES Employees(Emp_id)
);

CREATE TABLE Notifications (
    Notification_id INT AUTO_INCREMENT PRIMARY KEY,
    Target_type VARCHAR(20) NOT NULL,
    Target_id INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Message TEXT,
    Status VARCHAR(20) DEFAULT 'Unread',
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;

