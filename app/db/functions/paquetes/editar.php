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

if ($_SERVER["REQUEST_METHOD"] == "POST" && $codigo) {
    $status = $_POST['status'] ?? '';

    if (empty($status)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: El campo Status es obligatorio.'];
    } else {
        try {
            // Los nombres de columna (Status, Codigo) son correctos
            $stmt = $conn->prepare("UPDATE Paquete SET Status = ? WHERE Codigo = ?");
            $stmt->execute([$status, $codigo]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Status del paquete actualizado con éxito.'];
            } else {
                $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se pudo actualizar el status o no se encontró el paquete.'];
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al actualizar el status: ' . $e->getMessage()];
        }
    }
} else if (!$codigo) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó código de paquete para editar.'];
}

// Redirigir siempre de vuelta a la gestión
header('Location: /liberty/paquetes/gestion.php');
exit;
?>