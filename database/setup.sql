-- =====================================================
-- Wink & Glow Salon Booking - Complete Database Setup
-- Run this script to set up all required tables
-- =====================================================

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2),
    duration INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample services (Malawian Kwacha pricing)
INSERT INTO services (service_name, price, duration, description) VALUES
('Classic Lashes', 15000.00, 60, 'Natural-looking individual lash extensions'),
('Volume Lashes', 25000.00, 90, 'Fuller, more dramatic lash look with multiple extensions per natural lash'),
('Hybrid Lashes', 20000.00, 75, 'A mix of classic and volume techniques for a textured look'),
('Lash Lift', 12000.00, 45, 'Semi-permanent treatment that lifts and curls your natural lashes'),
('Lash Tint', 5000.00, 20, 'Darkens your natural lashes for a mascara-free look'),
('Lash Removal', 5000.00, 30, 'Safe removal of existing lash extensions')
ON DUPLICATE KEY UPDATE service_name = VALUES(service_name);

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    service INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    notes TEXT,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service) REFERENCES services(id) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Username: admin
-- Password: admin123 (change this in production!)
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@winkandglow.com')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Create notifications table for admin dashboard badge system
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'booking_created',
    is_seen TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seen_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    INDEX idx_is_seen (is_seen),
    INDEX idx_appointment_id (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create push_subscriptions table for storing admin push notification subscriptions
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint TEXT NOT NULL,
    p256dh TEXT NOT NULL,
    auth TEXT NOT NULL,
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_endpoint (endpoint(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Verification: Show created tables
-- =====================================================
SHOW TABLES;

-- Show services
SELECT * FROM services;

-- Show admin users (without password)
SELECT id, username, email, created_at FROM admin_users;
