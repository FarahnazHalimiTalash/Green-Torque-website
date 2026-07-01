-- Green Torque — MySQL database schema
-- Run this in Hostinger phpMyAdmin after creating your database.

CREATE TABLE IF NOT EXISTS appointments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  vehicle_year SMALLINT UNSIGNED NOT NULL,
  vehicle_make VARCHAR(60) NOT NULL,
  vehicle_model VARCHAR(80) NOT NULL,
  service VARCHAR(60) NOT NULL,
  hybrid_service VARCHAR(80) NULL,
  preferred_date DATE NOT NULL,
  preferred_time TIME NOT NULL,
  notes TEXT NULL,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_preferred_date (preferred_date),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
