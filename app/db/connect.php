<?php
$host = 'localhost'; 
$db   = 'le_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en lugar de errores/warnings
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa preparaciones nativas (más seguro)
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
    //echo "Conexión exitosa.";
} catch (\PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error: No se pudo conectar a la base de datos. Intente más tarde.");
}
?>