<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Validar que el usuario esté logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acceso no autorizado.'];
    header('Location: /paquetes/gestion.php');
    exit;
}

$codigo = $_GET['codigo'] ?? null; // El Código viene por la URL

if ($codigo) {
    try {
        $stmt = $conn->prepare("DELETE FROM Paquete WHERE Codigo = ?");
        $stmt->execute([$codigo]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Paquete eliminado con éxito.'];
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se pudo eliminar o no se encontró el paquete.'];
        }
    } catch (PDOException $e) {
        // Manejar error de llave foránea (si un paquete no se puede borrar por estar en otra tabla)
        if ($e->errorInfo[1] == 1451) {
             $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se puede eliminar el paquete porque tiene registros asociados (ej. historial).'];
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al eliminar el paquete: ' . $e->getMessage()];
        }
    }
} else {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó código de paquete para eliminar.'];
}

// Redirigir siempre de vuelta a la gestión
header('Location: /liberty/paquetes/gestion.php');
exit;
?>
