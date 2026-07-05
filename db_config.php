<?php
// db_config.php - Secure PDO Database Configuration

// In a real production environment, these variables would be loaded from the .env file.
$host = '127.0.0.1';
$db   = 'medic_vault_db';
$user = 'secure_db_user';
$pass = 'StrictDbPassword!#';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // This forces PHP to throw an exception if a DB error happens, instead of failing silently
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Fetches data as an associative array by default
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // CRITICAL FOR SECURITY: Disables emulated prepares to enforce true structural isolation (Stops SQLi)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Establishing the secure PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // We intentionally DO NOT echo $e->getMessage() to prevent leaking credentials to attackers
    die("Fatal Error: Database connection failed securely.");
}

