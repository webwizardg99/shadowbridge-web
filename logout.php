<?php
require_once __DIR__ . '/auth/db_config.php';
session_start_secure();
session_destroy();
setcookie(SESSION_NAME, '', time() - 3600, '/', '', true, true);
header('Location: /');
exit;
