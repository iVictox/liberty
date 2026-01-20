<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $id) {
    $nombre = $_POST['nombre'] ?? '';
    $estado = $_POST['estado'] ?? ''; 

    if (empty($nombre) || empty($estado)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: Todos los campos son obligatorios.'];
    } else {
        try {
            // Actualizamos nombre Y estado
            $stmt = $conn->prepare("UPDATE Origen SET Nombre = ?, Estado = ? WHERE Origen_id = ?");
            $stmt->execute([$nombre, $estado, $id]);
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Origen actualizado con éxito.'];
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }
} else if (!$id) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó ID.'];
}

header('Location: /liberty/gestion_origenes.php');
exit;
?>