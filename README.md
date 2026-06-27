# Inventra

A web-based inventory management system built with PHP and MySQL. Designed for small teams with different access levels — admins get full control, clients get what they need.

## Features

- Stock and inventory tracking
- Role-based access control (admin / client)
- User management
- File uploads with MIME-type validation
- CSRF protection and prepared statements throughout

## Tech Stack

- PHP
- MySQL
- HTML / CSS / JavaScript

## Setup

1. Clone the repo
```bash
   git clone https://github.com/vinn-devv/inventra.git
```

2. Import the database
   - Open phpMyAdmin
   - Create a new database named `inventra`
   - Import `inventra.sql`

3. Configure your connection
   - Copy `config.example.php` to `config.php`
   - Fill in your database credentials

4. Run it
   - Place the project folder in `htdocs` (XAMPP)
   - Start Apache and MySQL in XAMPP Control Panel
   - Visit `http://localhost/inventra`

## Default Accounts

After importing `inventra.sql`, this account is ready to use:

| Role  | Username | Password |
|-------|----------|----------|
| Admin | admin    | admin123 |

Log in at `http://localhost/inventra`  
Admins can manage inventory, users, and file uploads.  
Client accounts can be created through the admin panel.

## Project Structure
