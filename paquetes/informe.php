<?php
session_start();
// Control de sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// --- FILTROS ---
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual por defecto
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$origen_id = $_GET['origen_id'] ?? '';
$tipo_destino = $_GET['tipo_destino'] ?? '';

// Construcción de la Consulta
$sql = "
    SELECT 
        p.Codigo, 
        p.Fecha_Registro, 
        p.Status, 
        p.Estado,
        p.Motivo_Devolucion,
        o.Nombre AS OrigenNombre,
        d.Nombre AS DestinoNombre,
        d.Modalidad AS DestinoModalidad,
        u.nombre AS UserNombre,
        u.apellido AS UserApellido
    FROM Paquete p
    JOIN Origen o ON p.Origen_id = o.Origen_id
    JOIN Destino d ON p.Destino_id = d.Destino_id
    JOIN usuario u ON p.Usuario_id = u.id
    WHERE DATE(p.Fecha_Registro) BETWEEN ? AND ?
";

$params = [$fecha_inicio, $fecha_fin];

if (!empty($status)) {
    $sql .= " AND p.Status = ?";
    $params[] = $status;
}
if (!empty($origen_id)) {
    $sql .= " AND p.Origen_id = ?";
    $params[] = $origen_id;
}
if (!empty($tipo_destino)) {
    $sql .= " AND d.Modalidad = ?"; // Asumiendo que 'Modalidad' en Destino es 'Ruta' o 'Tienda'
    $params[] = $tipo_destino;
}

$sql .= " ORDER BY p.Fecha_Registro DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);

    // --- ESTADÍSTICAS RÁPIDAS DEL REPORTE ---
    $total_paquetes = count($resultados);
    $total_entregados = 0;
    $total_devoluciones = 0;
    $total_activos = 0;

    foreach ($resultados as $r) {
        if ($r->Status == 'Entregado') $total_entregados++;
        if ($r->Status == 'Devolución') $total_devoluciones++;
        if ($r->Estado == 1) $total_activos++;
    }
    
    // Calcular efectividad
    $efectividad = ($total_paquetes > 0) ? round(($total_entregados / $total_paquetes) * 100, 1) : 0;

} catch (PDOException $e) {
    die("Error en el reporte: " . $e->getMessage());
}

// Obtener orígenes para el filtro
$origenes = $conn->query("SELECT Origen_id, Nombre FROM Origen ORDER BY Nombre")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Operaciones - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/tables.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/forms.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filters-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-mini-card { background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #500101; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .stat-mini-card h3 { font-size: 24px; margin: 0; color: #333; }
        .stat-mini-card p { margin: 0; color: #64748b; font-size: 14px; }
        .btn-excel { background-color: #10893E; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; }
        .btn-excel:hover { background-color: #0d7535; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="table-header">
                <h1><i class="fas fa-chart-pie"></i> Informes y Estadísticas</h1>
            </div>

            <form method="GET" class="filters-box">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end;">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" class="form-control">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>" class="form-control">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="En Sede" <?php if($status=='En Sede') echo 'selected'; ?>>En Sede</option>
                            <option value="En Ruta" <?php if($status=='En Ruta') echo 'selected'; ?>>En Ruta</option>
                            <option value="Entregado" <?php if($status=='Entregado') echo 'selected'; ?>>Entregado</option>
                            <option value="Devolución" <?php if($status=='Devolución') echo 'selected'; ?>>Devolución</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Origen</label>
                        <select name="origen_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach($origenes as $org): ?>
                                <option value="<?php echo $org->Origen_id; ?>" <?php if($origen_id == $org->Origen_id) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($org->Nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Tipo Destino</label>
                        <select name="tipo_destino" class="form-control">
                            <option value="">Todos</option>
                            <option value="Ruta" <?php if($tipo_destino=='Ruta') echo 'selected'; ?>>Ruta</option>
                            <option value="Tienda" <?php if($tipo_destino=='Tienda') echo 'selected'; ?>>Tienda</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-submit" style="width: 100%;"><i class="fas fa-filter"></i> Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="stats-grid">
                <div class="stat-mini-card">
                    <div>
                        <h3><?php echo $total_paquetes; ?></h3>
                        <p>Total Registros</p>
                    </div>
                    <i class="fas fa-boxes fa-2x" style="color: #cbd5e1;"></i>
                </div>
                <div class="stat-mini-card" style="border-left-color: #10b981;">
                    <div>
                        <h3><?php echo $total_entregados; ?></h3>
                        <p>Entregados</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x" style="color: #d1fae5;"></i>
                </div>
                <div class="stat-mini-card" style="border-left-color: #ef4444;">
                    <div>
                        <h3><?php echo $total_devoluciones; ?></h3>
                        <p>Devoluciones</p>
                    </div>
                    <i class="fas fa-undo fa-2x" style="color: #fee2e2;"></i>
                </div>
                <div class="stat-mini-card" style="border-left-color: #3b82f6;">
                    <div>
                        <h3><?php echo $efectividad; ?>%</h3>
                        <p>Efectividad</p>
                    </div>
                    <i class="fas fa-chart-line fa-2x" style="color: #dbeafe;"></i>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="color: #500101; margin: 0;">Detalle de Operaciones</h3>
                
                <a href="/liberty/app/db/functions/paquetes/exportar_excel.php?<?php echo http_build_query($_GET); ?>" class="btn-excel" target="_blank">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Ruta (Origen -> Destino)</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr><td colspan="7" style="text-align: center; padding: 20px;">No se encontraron datos con estos filtros.</td></tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row->Codigo); ?></strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row->Fecha_Registro)); ?></td>
                                    <td><?php echo htmlspecialchars($row->UserNombre . ' ' . $row->UserApellido); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row->OrigenNombre); ?> 
                                        <i class="fas fa-arrow-right" style="font-size: 0.7em; color: #94a3b8;"></i> 
                                        <?php echo htmlspecialchars($row->DestinoNombre); ?>
                                    </td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row->DestinoModalidad); ?></span></td>
                                    <td><?php echo htmlspecialchars($row->Status); ?></td>
                                    <td>
                                        <?php if($row->Motivo_Devolucion): ?>
                                            <span style="color: #dc2626; font-size: 0.8em;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($row->Motivo_Devolucion); ?></span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>