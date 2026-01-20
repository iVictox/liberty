<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/auditoria/registrar.php'); // Incluir auditoría

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acceso no autorizado.'];
    header('Location: /paquetes/gestion.php');
    exit;
}

$codigo = $_GET['codigo'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $codigo) {
    $origen_id = $_POST['origen_id'] ?? '';
    $tipo_destino = $_POST['tipo_destino'] ?? '';
    $destino_id = $_POST['destino_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $estado = $_POST['estado'] ?? 1; 
    
    $motivo_devolucion = null;
    if ($status === 'Devolución') {
        $motivo_devolucion = $_POST['motivo_devolucion'] ?? '';
    }

    if (empty($origen_id) || empty($tipo_destino) || empty($destino_id) || empty($status)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: Todos los campos son obligatorios.'];
    } else {
        try {
            // --- OBTENER DATOS ANTERIORES PARA AUDITORÍA ---
            $stmtPrev = $conn->prepare("SELECT Status, Estado FROM Paquete WHERE Codigo = ?");
            $stmtPrev->execute([$codigo]);
            $prev = $stmtPrev->fetch(PDO::FETCH_OBJ);
            
            $sql = "UPDATE Paquete SET 
                    Origen_id = ?, 
                    Tipo_Destino_ID = ?, 
                    Destino_id = ?, 
                    Status = ?, 
                    Estado = ?,
                    Motivo_Devolucion = ? 
                    WHERE Codigo = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$origen_id, $tipo_destino, $destino_id, $status, $estado, $motivo_devolucion, $codigo]);
            
            // --- REGISTRAR AUDITORÍA ---
            if ($stmt->rowCount() > 0) {
                $detalles = "Paquete $codigo. Status: {$prev->Status} -> $status. Estado: {$prev->Estado} -> $estado.";
                if($status == 'Devolución') $detalles .= " Motivo: $motivo_devolucion";
                
                registrarAuditoria($conn, $_SESSION['user_id'], "Edición Paquete", $detalles);
                
                $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Paquete actualizado correctamente.'];
            } else {
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