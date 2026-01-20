<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó ID para eliminar.'];
} else {
    try {
        $stmt = $conn->prepare("DELETE FROM Destino WHERE Destino_id = ?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Destino eliminado con éxito.'];
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1451) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se puede eliminar, el destino está en uso.'];
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }
}

// Redirige de vuelta a la página de gestión
header('Location: /liberty/gestion_destinos.php');
exit;
?>
