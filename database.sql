-- ============================================================
-- DATABASE: auth_system
-- Description: Complete authentication system with users table
-- ============================================================

-- Drop database if exists (optional - remove if you want to keep existing data)
-- DROP DATABASE IF EXISTS auth_system;

-- Create database
CREATE DATABASE IF NOT EXISTS auth_system;
USE auth_system;

-- ============================================================
-- TABLE: users
-- Description: Stores all user accounts
-- ============================================================
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    country VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    privacy_agreed TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Admin Account
-- Password: Admin@123 (hashed using bcrypt)
-- ============================================================
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'Administrator',
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'male',
    'USA',
    'admin',
    1,
    NOW()
);

-- ============================================================
-- SEED DATA: Sample Users (for testing)
-- Password for all sample users: Admin@123
-- ============================================================

-- Sample User 1: John Doe
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'John Doe',
    'john@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'male',
    'USA',
    'user',
    1,
    NOW() - INTERVAL 1 DAY
);

-- Sample User 2: Jane Smith
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'Jane Smith',
    'jane@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'female',
    'Canada',
    'user',
    1,
    NOW() - INTERVAL 2 DAY
);

-- Sample User 3: Mike Johnson
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'Mike Johnson',
    'mike@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'male',
    'UK',
    'user',
    1,
    NOW() - INTERVAL 3 DAY
);

-- Sample User 4: Sarah Williams
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'Sarah Williams',
    'sarah@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'female',
    'Australia',
    'user',
    1,
    NOW() - INTERVAL 4 DAY
);

-- Sample User 5: David Brown
INSERT INTO users (full_name, email, password, gender, country, role, privacy_agreed, created_at) 
VALUES (
    'David Brown',
    'david@example.com',
    '$2y$12$nCUAUvAwTtrqlJBz65IxhunFEudrtShGqMpG07aav6Xvow1ZkV6OK',
    'male',
    'Germany',
    'user',
    1,
    NOW() - INTERVAL 5 DAY
);

-- ============================================================
-- VERIFICATION QUERIES (Run these to check your data)
-- ============================================================

-- Show all users
-- SELECT * FROM users;

-- Show admin user
-- SELECT * FROM users WHERE role = 'admin';

-- Show regular users
-- SELECT * FROM users WHERE role = 'user';

-- Count total users
-- SELECT COUNT(*) as total_users FROM users;

-- Count users by role
-- SELECT role, COUNT(*) as count FROM users GROUP BY role;

-- Show recent registrations
-- SELECT full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5;

-- ============================================================
-- CLEANUP (Optional - Use with caution!)
-- ============================================================

-- Delete all users except admin
-- DELETE FROM users WHERE id > 1;

-- Delete specific user
-- DELETE FROM users WHERE email = 'john@example.com';

-- Reset auto-increment
-- ALTER TABLE users AUTO_INCREMENT = 1;

-- ============================================================
-- GENERATE NEW PASSWORD HASH (If needed)
-- Run this PHP code to generate a new bcrypt hash:
-- ============================================================
/*
<?php
echo password_hash('YourPassword123', PASSWORD_BCRYPT);
?>
*/

-- ============================================================
-- END OF SQL FILE
-- ============================================================