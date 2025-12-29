<?php
// =============================
// DATABASE CONFIGURATION
// =============================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'mlbb_topup');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// =============================
// KHQR / BAKONG CONFIG
// =============================
define('API_TOKEN_BAKONG', getenv('BAKONG_API_TOKEN') ?: 'YOUR_API_TOKEN_HERE');
define('BANK_ACCOUNT', getenv('BANK_ACCOUNT') ?: 'chhira_ly@aclb');
define('PHONE_NUMBER', getenv('PHONE_NUMBER') ?: '855882000544');
define('TELEGRAM_BOT_TOKEN', '8236265021:AAHuNos6PJTHld-Zx8URV4RgQUGKlG7qzdg');
define('TELEGRAM_CHAT_ID', '-1003461060957');

// =============================
// ADMIN CONFIG
// =============================
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'admin');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');

// =============================
// SITE SETTINGS
// =============================
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');
define('PAYMENT_TIMEOUT', 180); // seconds

// =============================
// DATABASE CONNECTION
// =============================
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// =============================
// CREATE TABLES IF NOT EXISTS
// =============================
function initializeDatabase() {
    $pdo = getDBConnection();
    
    // Payment status table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_status (
        md5 VARCHAR(32) PRIMARY KEY,
        status ENUM('PENDING', 'PAID', 'EXPIRED') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        md5 VARCHAR(32) PRIMARY KEY,
        order_id VARCHAR(20) NOT NULL,
        status ENUM('SUCCESS', 'EXPIRED', 'PENDING') DEFAULT 'PENDING',
        game VARCHAR(50) NOT NULL,
        player_id VARCHAR(50),
        server_id VARCHAR(20),
        player_uid VARCHAR(50),
        username VARCHAR(100),
        item VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'KHQR',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        INDEX idx_created_at (created_at)
    )");
}

// Initialize database on first load
initializeDatabase();
?>