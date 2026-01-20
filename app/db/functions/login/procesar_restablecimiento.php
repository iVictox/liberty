<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: /liberty/login.php");
    exit;
}

$token = $_POST['token'];
$p_nueva = $_POST['p_nueva'];
$p_confirmar = $_POST['p_confirmar'];

// Validaciones
if ($p_nueva !== $p_confirmar) {
    die("Las contraseñas no coinciden.");
}
if (strlen($p_nueva) < 6) {
    die("La contraseña es muy corta.");
}

try {
    // 1. Verificar token de nuevo por seguridad
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE token_password = ? AND token_expiracion > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_OBJ);

    if ($usuario) {
        // 2. Actualizar contraseña, borrar token y APAGAR la bandera de 'requiere_cambio' (si estaba encendida)
        $hash = password_hash($p_nueva, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE usuario SET contraseña = ?, token_password = NULL, token_expiracion = NULL, requiere_cambio = 0 WHERE id = ?");
        $update->execute([$hash, $usuario->id]);

        $_SESSION['login_error'] = "¡Contraseña restablecida! Inicia sesión."; // Usamos login_error para mostrar mensaje en login (truco rápido) o crea un success msg.
        
        // Mensaje de éxito más limpio:
        echo "<script>alert('Contraseña actualizada correctamente.'); window.location.href='/liberty/login.php';</script>";
    } else {
        echo "Token inválido o expirado.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>