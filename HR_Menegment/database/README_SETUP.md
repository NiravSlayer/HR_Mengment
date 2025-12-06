# Database Setup Guide

## Quick Setup Steps

### 1. Create Database
```sql
CREATE DATABASE hrms_db;
USE hrms_db;
```

### 2. Import Tables
Import `hrms.sql` into your `hrms_db` database (via phpMyAdmin or MySQL command line).

### 3. Create Admin User

**Option A: Run PHP Script (Recommended)**
```
Open in browser: http://localhost/HR_Menegment/database/create_admin.php
```
OR via command line:
```bash
php database/create_admin.php
```

This will create:
- **Email**: `admin@hrms.local`
- **Password**: `Admin@123`

**Option B: Import Seed Data**
Import `seed_data.sql` after importing `hrms.sql`.

**Option C: Manual SQL Insert**
```sql
INSERT INTO HR_Admins (Hr_code, Hr_email, Hr_password, Hr_phone) 
VALUES (
  'HR001', 
  'admin@hrms.local', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  '1234567890'
);
```
Password: `Admin@123`

## Default Login Credentials

### HR Admin
- **Email**: `admin@hrms.local`
- **Password**: `Admin@123`

### Employee (if seed_data.sql imported)
- **Email**: `employee@hrms.local`
- **Password**: `Employee@123`

## Troubleshooting

If login fails:
1. Check database connection in `backend/config/config.php`
2. Verify database name is `hrms_db` (or update config)
3. Make sure `HR_Admins` table exists and has data
4. Run `database/create_admin.php` to create admin user with correct password hash

