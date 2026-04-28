# Mini Auth Project

A PHP/MySQL student portal and administration system built for managing users, course registrations, documents, finances, and academic records.

## Overview

This project is a student authentication portal with separate student and admin workflows.
- Students log in with their registration number and password.
- Admins manage students, courses, faculties, semesters, registrations, documents, payments, and reports.

## Key Features

- User authentication and session management
- Role-based access for students and administrators
- Student dashboard with profile details and document access
- Admin management panels for students, courses, faculties, programs, units, registrations, and finance
- Password reset and forgot-password support
- Document upload and download support for student-related files
- MySQL database schema with sample tables and initial data in `auth.sql`

## Requirements

- PHP (recommended PHP 8+)
- MySQL / MariaDB
- Web server such as Apache (e.g. XAMPP)
- Composer

## Installation

1. Place the project folder inside your web server root, for example:
   - `C:\xampp\htdocs\mini-auth-project`

2. Install Composer dependencies:

```bash
cd C:\xampp\htdocs\mini-auth-project
composer install
```

3. Create the database and import the schema:
   - Import `auth.sql` into MySQL using phpMyAdmin or the MySQL CLI.

4. Update database settings in `config.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db = "auth";
```

5. If you plan to use email password recovery, add SMTP configuration in `config.php`.

## Running the Project

Open the project in your browser:

```text
http://localhost/mini-auth-project/login.php
```

## Default Access

The database import includes a seeded admin user with registration number `ADMIN001`.
- Email: `admin@berrywasonga.com`
- Reg No: `ADMIN001`

> The stored password is hashed in `auth.sql`. If you cannot log in, use the password reset flow or manually update the password in the `users` table.

## Project Structure

- `login.php` - public login page for students and admins
- `config.php` - database and SMTP configuration
- `auth.sql` - database schema and seed data
- `admin/` - admin dashboard and management interfaces
- `students/` - student portal pages and views
- `uploads/` - uploaded document storage
- `vendor/` - Composer dependencies

## Notes

- The root `index.php` currently redirects users to `login.php` if no valid session exists.
- For a live deployment, secure `config.php`, restrict access to uploads, and enable HTTPS.
- If you change the database user or password, update `config.php` accordingly.

## Contact

If you want to extend this project, use the existing folder structure as a guide and keep role protection logic in place.
