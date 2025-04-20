<?php
// Application Configuration
define('APP_NAME', 'File Management System');
define('APP_URL', 'http://localhost/file-management-system');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// PHPMailer Configuration
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'syedsunnygalibbasha@gmail.com');
define('MAIL_PASSWORD', 'vxjgployyjfrajza');
define('MAIL_FROM', 'syedsunnygalibbasha@gmail.com');
define('MAIL_FROM_NAME', 'File Management System');
define('MAIL_PORT', 587);
define('MAIL_SMTP_AUTH', true);
define('MAIL_SMTP_SECURE', 'tls');
?>