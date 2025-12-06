HR Management System (HRMS)
===========================

This project is a web-based HR Management System with two roles:

- HR Admin
- Employee

The system follows your specification:

- Exactly 10 database tables (no extras)
- PHP backend in `backend/`
- Bootstrap-based frontend in `frontend/`

## Tech Stack

- PHP 8+ (or compatible version on XAMPP)
- MySQL / MariaDB
- Bootstrap 5 (CDN)
- Vanilla JS (can be replaced/augmented later)

## Project Structure

- `backend/` – PHP backend (controllers, models, routes, middleware, helpers, config).
- `frontend/` – Bootstrap UI, pages for Admin and Employee.
- `database/` – `hrms.sql` schema + migrations/seeds (optional).
- `uploads/` – stored files (`documents/`, `profiles/`).

## Setup

1. Create a MySQL database, e.g. `hrms_db`.
2. Import `database/hrms.sql`.
3. Configure DB credentials in `backend/config/config.php`.
4. Point your web server document root to the project and route requests through `backend/index.php` (or use Apache virtual host).
5. Open `frontend/index.html` in browser for the initial UI shell (login page).

This is an initial scaffold; modules (Employees, Attendance, Leaves, Tasks, Projects, Documents, Notifications) can now be implemented on top of this.


