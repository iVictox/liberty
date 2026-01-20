<?php
// 1. Configurar Zona Horaria de PHP (Venezuela)
date_default_timezone_set('America/Caracas');

$host = 'localhost'; 
$db   = 'le_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
    
    // 2. Sincronizar MySQL también a Venezuela (UTC-4)
    // Esto asegura que las funciones NOW() de SQL coincidan con time() de PHP
    $conn->exec("SET time_zone = '-04:00';");
    
} catch (\PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error: No se pudo conectar a la base de datos.");
}
?>