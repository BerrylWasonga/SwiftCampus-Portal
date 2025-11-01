-- SQL script to create a 'users' table
-- Full users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL DEFAULT '',
    last_name VARCHAR(50) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample users (password: 'password' for both - hashed)
INSERT INTO users (first_name, last_name, email, password, role, status) VALUES 
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');

INSERT INTO users (first_name, last_name, email, password, role, status) VALUES 
('Admin', 'User', 'admin@berrywasonga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');