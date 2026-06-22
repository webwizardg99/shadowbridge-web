<?php
// ShadowBridge — One-time DB setup script
// Run once: https://shadowbridge.store/auth/setup.php?key=SETUP_SECRET
// Then DELETE this file from the server!

define('SETUP_KEY', 'NOX_SETUP_2026_DELETE_AFTER_RUN');

require_once __DIR__ . '/db_config.php';

if (($_GET['key'] ?? '') !== SETUP_KEY) {
    http_response_code(403);
    die('Forbidden. Provide ?key= parameter.');
}

try {
    $pdo = db_connect();

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email       VARCHAR(255) NOT NULL UNIQUE,
        username    VARCHAR(60)  NOT NULL UNIQUE,
        password    VARCHAR(255) NOT NULL,
        plan        ENUM('free','pro','arsenal') NOT NULL DEFAULT 'free',
        verified    TINYINT(1) NOT NULL DEFAULT 0,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_login  DATETIME NULL,
        ip_address  VARCHAR(45) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id     INT UNSIGNED NOT NULL,
        token       VARCHAR(64) NOT NULL UNIQUE,
        ip_address  VARCHAR(45) NULL,
        user_agent  VARCHAR(255) NULL,
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at  DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo '<pre style="color:lime;background:#0d1117;padding:20px;font-family:monospace;">';
    echo "✅ Table 'users' created/verified\n";
    echo "✅ Table 'sessions' created/verified\n";
    echo "\n⚠️  DELETE this file from the server now!\n";
    echo "    rm /home/ghostwizardg/public_html/auth/setup.php\n";
    echo '</pre>';

} catch (Exception $e) {
    echo '<pre style="color:red;background:#0d1117;padding:20px;">';
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "\nCheck db_config.php — DB credentials might be wrong.";
    echo '</pre>';
}
