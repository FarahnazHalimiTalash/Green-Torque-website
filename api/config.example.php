<?php
/**
 * Copy this file to config.php and fill in your Hostinger credentials.
 *
 * Hostinger setup:
 * 1. hPanel → Databases → create MySQL database + user
 * 2. phpMyAdmin → import database/schema.sql
 * 3. Copy this file to config.php with your values
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

define('NOTIFY_EMAIL', 'Info@greentorqueev.com');
define('EMAIL_FROM', 'noreply@greentorqueev.com');
define('BUSINESS_NAME', 'Green Torque Hybrid/EV Repair');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'change-this-password');

define('GOOGLE_PLACES_API_KEY', 'YOUR_GOOGLE_API_KEY_HERE');
define('PLACE_TEXT_QUERY', 'Green Torque Hybrid/EV Repair and Services 2031 Fulton Avenue Sacramento CA 95825');

define('TIMEZONE', 'America/Los_Angeles');
