<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Verificar método y sesión
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: /liberty/cambiar_clave.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /liberty/login.php");
    exit;
}

$p_nueva = $_POST['p_nueva'] ?? '';
$p_confirmar = $_POST['p_confirmar'] ?? '';
$usuario_id = $_SESSION['user_id'];

// 1. Validaciones
if (empty($p_nueva) || empty($p_confirmar)) {
    $_SESSION['cambio_error'] = 'Todos los campos son obligatorios.';
    header("Location: /liberty/cambiar_clave.php");
    exit;
}

if (strlen($p_nueva) < 6) {
    $_SESSION['cambio_error'] = 'La contraseña es muy corta (mínimo 6 caracteres).';
    header("Location: /liberty/cambiar_clave.php");
    exit;
}

if ($p_nueva !== $p_confirmar) {
    $_SESSION['cambio_error'] = 'Las contraseñas no coinciden.';
    header("Location: /liberty/cambiar_clave.php");
    exit;
}

try {
    // 2. Encriptar la nueva contraseña
    $hash_nueva = password_hash($p_nueva, PASSWORD_DEFAULT);

    // 3. Actualizar DB: Guardar clave y APAGAR la bandera (requiere_cambio = 0)
    $stmt = $conn->prepare("UPDATE usuario SET contraseña = ?, requiere_cambio = 0 WHERE id = ?");
    $stmt->execute([$hash_nueva, $usuario_id]);

    // 4. Redirigir al Dashboard
    // Opcional: Puedes poner un mensaje de bienvenida en el index si quieres
    header("Location: /liberty/"); 
    exit;

} catch (PDOException $e) {
    $_SESSION['cambio_error'] = 'Error al actualizar: ' . $e->getMessage();
    header("Location: /liberty/cambiar_clave.php");
    exit;
}
?>