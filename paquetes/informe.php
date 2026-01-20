<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// --- LÓGICA DE DATOS ---
try {
    // 1. Total Global
    $total = $conn->query("SELECT COUNT(*) FROM Paquete")->fetchColumn();
    
    // 2. Desglose por Status Básico
    $en_sede = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'En Sede'")->fetchColumn();
    $entregados = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'Entregado'")->fetchColumn();
    $devolucion = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'Devolución'")->fetchColumn();

    // 3. Status Específicos (Logística)
    // En Ruta (Solo destinos tipo Ruta)
    $en_ruta = $conn->query("
        SELECT COUNT(*) 
        FROM Paquete p
        JOIN Destino d ON p.Destino_id = d.Destino_id
        WHERE p.Status = 'En Ruta' AND d.Modalidad = 'Ruta'
    ")->fetchColumn();

    // Transferidos (Solo destinos tipo Tienda)
    $transferido = $conn->query("
        SELECT COUNT(*) 
        FROM Paquete p
        JOIN Destino d ON p.Destino_id = d.Destino_id
        WHERE p.Status = 'Transferido' AND d.Modalidad = 'Tienda'
    ")->fetchColumn();

    // Calcular porcentajes simples para barra de progreso (opcional)
    $porc_entregados = $total > 0 ? round(($entregados / $total) * 100) : 0;
    $porc_problemas = $total > 0 ? round(($devolucion / $total) * 100) : 0;

} catch (PDOException $e) {
    die("Error al consultar estadísticas: " . $e->getMessage());
}

// Fecha actual para el reporte
$fecha_reporte = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Operativo - Liberty Express</title>
    
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/dashboard.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Estilos extra solo para esta página de informes */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        /* Colores adicionales para iconos que no estaban en dashboard.css */
        .icon-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .icon-purple { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
        .icon-dark { background: rgba(30, 41, 59, 0.1); color: #1e293b; }

        /* Estilo para impresión */
        @media print {
            .sidebar, .toggle-btn, .btn-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .welcome-box { box-shadow: none; border: 1px solid #ddd; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            
            <div class="report-header">
                <div>
                    <h2 style="margin:0; color:var(--primary);">Informe Operativo</h2>
                    <span style="color: #64748b; font-size: 0.9rem;">Generado el: <?php echo $fecha_reporte; ?></span>
                </div>
                <button onclick="window.print()" class="action-btn btn-print" style="cursor: pointer;">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
            </div>

            <section class="welcome-box" style="padding: 1.5rem; min-height: auto;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 2.5rem; margin: 0;"><?php echo $total; ?></h1>
                        <p style="opacity: 0.9;">Paquetes totales registrados en el sistema</p>
                    </div>
                    <div style="text-align: right; display: none; display: md-block;">
                        <i class="fas fa-cubes" style="font-size: 3rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </section>

            <div class="dashboard-grid">

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">En Almacén</h4>
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $en_sede; ?></h2>
                    <div class="stat-footer">
                        <span class="text-gray">Pendientes por procesar</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">En Ruta</h4>
                        <div class="stat-icon icon-orange">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $en_ruta; ?></h2>
                    <div class="stat-footer">
                        <span class="text-gray">En tránsito a destino</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">Transferidos</h4>
                        <div class="stat-icon icon-purple">
                            <i class="fas fa-store-alt"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $transferido; ?></h2>
                    <div class="stat-footer">
                        <span class="text-gray">En tiendas aliadas</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">Entregados</h4>
                        <div class="stat-icon icon-green">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $entregados; ?></h2>
                    <div class="stat-footer">
                        <span class="trend-up"><?php echo $porc_entregados; ?>%</span> del total global
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">Devoluciones</h4>
                        <div class="stat-icon icon-red">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $devolucion; ?></h2>
                    <div class="stat-footer">
                        <span class="trend-down"><?php echo $porc_problemas; ?>%</span> incidencia
                    </div>
                </div>

                <div class="stat-card" style="background: #f8fafc; border: 2px dashed #e2e8f0;">
                    <div class="stat-header">
                        <h4 class="stat-title">Efectividad</h4>
                        <div class="stat-icon icon-dark">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 5px;">
                            <span>Éxito</span>
                            <strong><?php echo $porc_entregados; ?>%</strong>
                        </div>
                        <div style="width: 100%; background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                            <div style="width: <?php echo $porc_entregados; ?>%; background: #10b981; height: 100%;"></div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div style="text-align: center; color: #94a3b8; font-size: 0.8rem; margin-top: 2rem;">
                &copy; <?php echo date('Y'); ?> Liberty Express - Sistema de Gestión Logística v2.0
            </div>

        </main> 
    </div> 
    
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>