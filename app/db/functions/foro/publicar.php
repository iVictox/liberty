<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = trim($_POST['mensaje']);
    $origen = $_POST['origen'] ?? 'foro';
    $usuario_id = $_SESSION['user_id'];
    
    // Generamos la fecha desde PHP (ya sincronizado a Caracas)
    $fecha_actual = date('Y-m-d H:i:s');

    if (!empty($mensaje)) {
        try {
            // Usamos ? para la fecha en lugar de NOW()
            $stmt = $conn->prepare("INSERT INTO foro_mensajes (usuario_id, mensaje, fecha) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $mensaje, $fecha_actual]);
        } catch (PDOException $e) {
            // Error silencioso
        }
    }

    if ($origen === 'dashboard') {
        header('Location: /liberty/index.php');
    } else {
        header('Location: /liberty/foro.php');
    }
    exit;
}

header('Location: /liberty/foro.php');
exit;
?>