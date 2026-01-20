<?php
session_start();

// Validar sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// Validar ROL DE ADMINISTRADOR (Asumiendo que rol 3 es Admin según tus archivos anteriores)
if ($_SESSION['user_rol'] != 3) {
    echo "Acceso Denegado. No tienes permisos para ver esta página.";
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Consulta de auditoría con nombre de usuario
$sql = "SELECT a.*, u.nombre, u.apellido, u.correo 
        FROM auditoria a 
        LEFT JOIN usuario u ON a.usuario_id = u.id 
        ORDER BY a.fecha DESC LIMIT 100";
try {
    $registros = $conn->query($sql)->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/tables.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="table-container">
                <div class="table-header">
                    <h1><i class="fas fa-history"></i> Auditoría del Sistema</h1>
                    <span style="font-size: 0.9rem; color: #64748b;">Últimos 100 movimientos</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registros)): ?>
                                <tr><td colspan="5" style="text-align: center;">No hay registros de auditoría.</td></tr>
                            <?php else: ?>
                                <?php foreach ($registros as $log): ?>
                                    <tr>
                                        <td style="white-space: nowrap;">
                                            <?php echo date('d/m/Y H:i', strtotime($log->fecha)); ?>
                                        </td>
                                        <td>
                                            <?php if($log->nombre): ?>
                                                <strong><?php echo htmlspecialchars($log->nombre . ' ' . $log->apellido); ?></strong><br>
                                                <small><?php echo htmlspecialchars($log->correo); ?></small>
                                            <?php else: ?>
                                                <span style="color: #999;">Usuario Eliminado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge" style="background:#e0f2fe; color:#0369a1;">
                                                <?php echo htmlspecialchars($log->accion); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($log->detalles); ?>
                                        </td>
                                        <td style="font-size: 0.8rem; color: #64748b;">
                                            <?php echo htmlspecialchars($log->ip); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>