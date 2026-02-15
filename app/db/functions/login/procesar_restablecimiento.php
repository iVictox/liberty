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

if ($p_nueva !== $p_confirmar) die("Las contraseñas no coinciden.");
if (strlen($p_nueva) < 6) die("La contraseña es muy corta.");

try {
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE token_password = ? AND token_expiracion > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_OBJ);

    if ($usuario) {
        $hash = password_hash($p_nueva, PASSWORD_DEFAULT);
        
        // --- CORRECCIÓN: ESTADO = 1 (REACTIVA CUENTA) ---
        $update = $conn->prepare("UPDATE usuario SET contraseña = ?, token_password = NULL, token_expiracion = NULL, requiere_cambio = 0, estado = 1 WHERE id = ?");
        $update->execute([$hash, $usuario->id]);
        // ------------------------------------------------

        echo "<script>alert('Contraseña actualizada y cuenta reactivada correctamente.'); window.location.href='/liberty/login.php';</script>";
    } else {
        echo "Token inválido o expirado.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>