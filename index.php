<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// 1. Incluir Archivos
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/dashboard/stats.php');

// 2. Obtener Datos y Calcular Tendencias

// --- KPI 1: INGRESOS (Comparativa Hoy vs Ayer) ---
$ingresosHoy = getIngresosHoy($conn);
$ingresosAyer = getIngresosAyer($conn);
$tendenciaIngresos = calcularTendencia($ingresosHoy, $ingresosAyer);
// Determinar clase y flecha
$claseTendenciaIng = ($tendenciaIngresos >= 0) ? 'trend-up' : 'trend-down';
$iconoTendenciaIng = ($tendenciaIngresos >= 0) ? 'fa-arrow-up' : 'fa-arrow-down';
$textoTendenciaIng = ($tendenciaIngresos >= 0) ? '+' . $tendenciaIngresos . '%' : $tendenciaIngresos . '%';


// --- KPI 2: EN ALMACÉN (Capacidad Operativa) ---
$enSede = getPaquetesEnSede($conn);
$totalActivos = getTotalPaquetesActivos($conn);
// Calculamos qué porcentaje del total de paquetes está estancado en sede
$porcentajeOcupacion = ($totalActivos > 0) ? round(($enSede / $totalActivos) * 100) : 0;


// --- KPI 3: EN RUTA (Actividad) ---
$enRuta = getPaquetesEnRuta($conn);
// Simplemente mostramos el número activo.


// --- KPI 4: ENTREGADOS (Comparativa Mes vs Mes Anterior) ---
$entregadosMes = getEntregadosEsteMes($conn);
$entregadosMesAnt = getEntregadosMesAnterior($conn);
$tendenciaEntregas = calcularTendencia($entregadosMes, $entregadosMesAnt);
// Determinar clase y flecha
$claseTendenciaEnt = ($tendenciaEntregas >= 0) ? 'trend-up' : 'trend-down';
$iconoTendenciaEnt = ($tendenciaEntregas >= 0) ? 'fa-arrow-up' : 'fa-arrow-down';
$textoTendenciaEnt = ($tendenciaEntregas >= 0) ? '+' . $tendenciaEntregas . '%' : $tendenciaEntregas . '%';


// Datos de usuario
$nombreUsuario = $_SESSION['user_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberty Express - Dashboard</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="app-wrap">

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>

        <main class="main-content">

            <section class="welcome-box">
                <h1>Hola, <?php echo htmlspecialchars($nombreUsuario); ?> 👋</h1>
                <p>Aquí tienes el resumen de operaciones en tiempo real.</p>
            </section>

            <div class="dashboard-grid" style="padding-bottom: 1rem; gap: 1rem;">
                <div class="quick-actions">
                    <a href="/liberty/paquetes/gestion.php" class="action-btn">
                        <i class="fas fa-box-open"></i> Registrar Paquete
                    </a>
                    <a href="/liberty/paquetes/gestion.php" class="action-btn">
                        <i class="fas fa-search"></i> Buscar Envío
                    </a>
                    <a href="/liberty/paquetes/informe.php" class="action-btn">
                        <i class="fas fa-file-alt"></i> Ver Informes
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">Registros Hoy</h4>
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $ingresosHoy; ?></h2>
                    <div class="stat-footer">
                        <span class="<?php echo $claseTendenciaIng; ?>">
                            <i class="fas <?php echo $iconoTendenciaIng; ?>"></i> <?php echo $textoTendenciaIng; ?>
                        </span> 
                        <span class="text-gray">vs ayer (<?php echo $ingresosAyer; ?>)</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">En Sede</h4>
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $enSede; ?></h2>
                    <div class="stat-footer">
                        <span class="text-gray"><?php echo $porcentajeOcupacion; ?>% de la carga activa total</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">En Tránsito</h4>
                        <div class="stat-icon icon-orange">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $enRuta; ?></h2> 
                    <div class="stat-footer">
                        <span class="text-gray">Despachos activos ahora</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title">Entregas (Mes)</h4>
                        <div class="stat-icon icon-green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <h2 class="stat-value"><?php echo $entregadosMes; ?></h2>
                    <div class="stat-footer">
                        <span class="<?php echo $claseTendenciaEnt; ?>">
                            <i class="fas <?php echo $iconoTendenciaEnt; ?>"></i> <?php echo $textoTendenciaEnt; ?>
                        </span>
                        <span class="text-gray">vs mes anterior</span>
                    </div>
                </div>

                <div class="forum-card" style="grid-column: span 12;">
                    <div class="card-header">
                        <h3><i class="fas fa-bullhorn"></i> Novedades Operativas</h3>
                    </div>
                    <div class="card-body">
                        <div class="posts">
                            <div class="post">
                                <div class="post-header">
                                    <strong>Sistema Automático</strong>
                                    <span>Hoy</span>
                                </div>
                                <div class="post-body">
                                    Las estadísticas del dashboard se han actualizado correctamente. Los porcentajes reflejan la actividad en tiempo real comparada con el periodo anterior.
                                </div>
                            </div>
                        </div>
                        
                        <form class="post-form" onsubmit="alert('Función de postear en desarrollo'); return false;">
                            <textarea name="content" rows="2" placeholder="Escribe una novedad..."></textarea>
                            <button class="btn-post">Publicar</button>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
    
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>