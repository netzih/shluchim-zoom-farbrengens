-- ============================================
-- Shluchim Zoom Farbrengens Database Schema
-- ============================================

-- Create database (optional - you may create this in Plesk)
-- CREATE DATABASE IF NOT EXISTS shluchim_farbrengens CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE shluchim_farbrengens;

-- ============================================
-- Events Table
-- ============================================

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    farbrenger VARCHAR(255) DEFAULT NULL,
    occasion VARCHAR(100) DEFAULT NULL,
    event_date VARCHAR(255) DEFAULT NULL,
    zoom_link VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_occasion (occasion),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Admin Users Table
-- ============================================

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    organization VARCHAR(255) DEFAULT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Site Settings Table
-- ============================================

CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT DEFAULT NULL,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Site Settings
-- ============================================

INSERT INTO site_settings (setting_key, setting_value) VALUES
    ('primary_color', '#6B2C3E'),
    ('secondary_color', '#E67E22'),
    ('site_title', 'Shluchim Zoom Farbrengens'),
    ('header_line1', 'Shluchim Zoom'),
    ('header_line2', 'Farbrengens'),
    ('header_description', 'Join live Farbrengens with Shluchim from around the world')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- ============================================
-- END OF SCHEMA
-- ============================================
