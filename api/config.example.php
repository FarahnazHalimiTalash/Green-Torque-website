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

define('NOTIFY_EMAIL', 'info@greentorqueev.com');
define('EMAIL_FROM', 'info@greentorqueev.com');
define('BUSINESS_NAME', 'Green Torque Hybrid/EV Repair');

// Owner email when a customer books (recommended: Hostinger SMTP)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'info@greentorqueev.com');
define('SMTP_PASS', 'your-email-account-password');

// Backup if SMTP/php mail fail (first FormSubmit email may require activation link)
define('USE_FORMSUBMIT_NOTIFY', true);

// One-time email test URL: https://yoursite.com/api/email-test.php?key=your-secret-key
define('EMAIL_TEST_KEY', 'change-this-test-key');

// SMS to business owner (optional — requires Twilio account: https://www.twilio.com)
define('NOTIFY_PHONE', '+19168969086');
define('TWILIO_ACCOUNT_SID', '');
define('TWILIO_AUTH_TOKEN', '');
define('TWILIO_FROM_NUMBER', '');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'change-this-password');

define('GOOGLE_PLACES_API_KEY', 'YOUR_GOOGLE_API_KEY_HERE');
define('PLACE_TEXT_QUERY', 'Green Torque Hybrid/EV Repair and Services 2031 Fulton Avenue Sacramento CA 95825');

define('TIMEZONE', 'America/Los_Angeles');
