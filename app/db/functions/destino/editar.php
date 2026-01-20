<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$id = $_GET['id'] ?? null; // El ID viene por la URL

if ($_SERVER["REQUEST_METHOD"] == "POST" && $id) {
    $nombre = $_POST['nombre'] ?? '';
    $modalidad = $_POST['modalidad'] ?? '';
    $estado = $_POST['estado'] ?? '';

    if (empty($nombre) || empty($modalidad) || empty($estado)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: Todos los campos son obligatorios.'];
    } else {
        try {
            $stmt = $conn->prepare("UPDATE Destino SET Nombre = ?, Modalidad = ?, Estado = ? WHERE Destino_id = ?");
            $stmt->execute([$nombre, $modalidad, $estado, $id]);
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Destino actualizado con éxito.'];
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al actualizar el destino: ' . $e->getMessage()];
        }
    }
} else if (!$id) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó ID para editar.'];
}

// Redirige de vuelta a la página de gestión
header('Location: /liberty/gestion_destinos.php');
exit;
?>
