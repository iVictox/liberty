<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó ID.'];
} else {
    try {
        $stmt = $conn->prepare("DELETE FROM Origen WHERE Origen_id = ?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Origen eliminado con éxito.'];
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1451) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se puede eliminar, el origen está en uso.'];
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }
}
header('Location: /liberty/gestion_origenes.php'); // Redirige al UI de Orígenes
exit;
?>
