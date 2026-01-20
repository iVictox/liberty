<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Validar sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acceso no autorizado.'];
    header('Location: /paquetes/gestion.php');
    exit;
}

$codigo = $_GET['codigo'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $codigo) {
    // Recibir todos los datos del formulario
    $origen_id = $_POST['origen_id'] ?? '';
    $tipo_destino = $_POST['tipo_destino'] ?? '';
    $destino_id = $_POST['destino_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $estado = $_POST['estado'] ?? 1; // 1 = Activo por defecto

    if (empty($origen_id) || empty($tipo_destino) || empty($destino_id) || empty($status)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: Todos los campos son obligatorios.'];
    } else {
        try {
            // Actualizamos TODO excepto el Código y la Fecha de Registro
            $sql = "UPDATE Paquete SET 
                    Origen_id = ?, 
                    Tipo_Destino_ID = ?, 
                    Destino_id = ?, 
                    Status = ?, 
                    Estado = ? 
                    WHERE Codigo = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$origen_id, $tipo_destino, $destino_id, $status, $estado, $codigo]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Paquete actualizado correctamente.'];
            } else {
                // A veces no hay cambios reales, pero la query se ejecutó bien
                $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Datos guardados (Sin cambios detectados).'];
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }
} else if (!$codigo) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó código.'];
}

header('Location: /liberty/paquetes/gestion.php');
exit;
?>