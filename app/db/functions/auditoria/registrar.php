<?php
// Función global para registrar acciones en la auditoría
function registrarAuditoria($conn, $usuario_id, $accion, $detalles = '') {
    try {
        // Obtener IP del cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $sql = "INSERT INTO auditoria (usuario_id, accion, detalles, ip, fecha) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id, $accion, $detalles, $ip]);
        
        return true;
    } catch (PDOException $e) {
        // Silenciosamente fallar o loguear en archivo de texto para no romper el flujo del usuario
        error_log("Error al registrar auditoría: " . $e->getMessage());
        return false;
    }
}
?>