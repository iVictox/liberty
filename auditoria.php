<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// Solo Admin puede ver auditoría
if ($_SESSION['user_rol'] != 3) {
    header('Location: /liberty/');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// --- Paginación ---
$registros_por_pagina = 15;
$pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// --- Consulta con JOIN para obtener la FOTO ---
$sql = "SELECT a.*, u.nombre, u.apellido, u.correo, u.foto_perfil 
        FROM auditoria a 
        LEFT JOIN usuario u ON a.usuario_id = u.id 
        ORDER BY a.fecha DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_OBJ);

// Contar total para paginación
$total_stmt = $conn->query("SELECT COUNT(*) FROM auditoria");
$total_registros = $total_stmt->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/tables.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .audit-user { display: flex; align-items: center; gap: 10px; }
        .audit-avatar { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; background: #eee; border: 1px solid #ddd; }
        .audit-avatar-initials { width: 35px; height: 35px; border-radius: 50%; background: #500101; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
        .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; }
        .page-link { padding: 8px 12px; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 4px; background: white; }
        .page-link.active { background: #500101; color: white; border-color: #500101; }
        .page-link:hover:not(.active) { background: #f1f1f1; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="table-header">
                <h1><i class="fas fa-history"></i> Registro de Auditoría</h1>
                <p>Historial de acciones realizadas por los usuarios en el sistema.</p>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha / Hora</th>
                            <th>Usuario Responsable</th>
                            <th>Acción</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">No hay registros de auditoría.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log->id; ?></td>
                                    <td style="white-space: nowrap;"><?php echo date('d/m/Y h:i A', strtotime($log->fecha)); ?></td>
                                    <td>
                                        <div class="audit-user">
                                            <?php if (!empty($log->foto_perfil) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/assets/uploads/perfiles/' . $log->foto_perfil)): ?>
                                                <img src="/liberty/app/assets/uploads/perfiles/<?php echo $log->foto_perfil; ?>" class="audit-avatar" alt="Foto">
                                            <?php else: ?>
                                                <div class="audit-avatar-initials">
                                                    <?php echo strtoupper(substr($log->nombre ?? '?', 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars(($log->nombre ?? 'Usuario') . ' ' . ($log->apellido ?? 'Eliminado')); ?></div>
                                                <div style="font-size: 0.8em; color: #666;"><?php echo htmlspecialchars($log->correo ?? '---'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($log->accion); ?></span></td>
                                    <td style="font-size: 0.9em; max-width: 400px;"><?php echo htmlspecialchars($log->detalles); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pag=<?php echo $i; ?>" class="page-link <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>