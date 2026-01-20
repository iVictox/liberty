<?php
session_start();
// --- LÓGICA DE CADUCIDAD DE SESIÓN (30 MINUTOS) ---
$timeout_duration = 1800; 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /liberty/login.php?mensaje=sesion_expirada");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/dashboard/stats.php');

// --- FUNCIÓN DE TIEMPO ---
function haceCuantoDash($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Hace un momento';
    if ($diff < 3600) return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Hace ' . floor($diff / 3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff / 86400) . ' días';
    return date('d/m/Y', $time);
}

// --- KPI DATA ---
$ingresosHoy = getIngresosHoy($conn);
$ingresosAyer = getIngresosAyer($conn);
$tendenciaIngresos = calcularTendencia($ingresosHoy, $ingresosAyer);
$claseTendenciaIng = ($tendenciaIngresos >= 0) ? 'trend-up' : 'trend-down';
$iconoTendenciaIng = ($tendenciaIngresos >= 0) ? 'fa-arrow-up' : 'fa-arrow-down';
$textoTendenciaIng = ($tendenciaIngresos >= 0) ? '+' . $tendenciaIngresos . '%' : $tendenciaIngresos . '%';

$enSede = getPaquetesEnSede($conn);
$totalActivos = getTotalPaquetesActivos($conn);
$porcentajeOcupacion = ($totalActivos > 0) ? round(($enSede / $totalActivos) * 100) : 0;
$enRuta = getPaquetesEnRuta($conn);

$entregadosMes = getEntregadosEsteMes($conn);
$entregadosMesAnt = getEntregadosMesAnterior($conn);
$tendenciaEntregas = calcularTendencia($entregadosMes, $entregadosMesAnt);
$claseTendenciaEnt = ($tendenciaEntregas >= 0) ? 'trend-up' : 'trend-down';
$iconoTendenciaEnt = ($tendenciaEntregas >= 0) ? 'fa-arrow-up' : 'fa-arrow-down';
$textoTendenciaEnt = ($tendenciaEntregas >= 0) ? '+' . $tendenciaEntregas . '%' : $tendenciaEntregas . '%';

// --- FORO DASHBOARD ---
// 1. Obtenemos los 3 más recientes (DESC)
$sqlForo = "SELECT f.*, u.nombre, u.apellido FROM foro_mensajes f JOIN usuario u ON f.usuario_id = u.id ORDER BY f.fecha DESC LIMIT 3";
$mensajesDash = $conn->query($sqlForo)->fetchAll(PDO::FETCH_OBJ);

// 2. CAMBIO AQUÍ: Invertimos el array para mostrarlos cronológicamente (Antiguo -> Nuevo)
$mensajesDash = array_reverse($mensajesDash);

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
    <link rel="stylesheet" href="/liberty/app/assets/css/forum.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>

        <main class="main-content">

            <section class="welcome-box">
                <h1>Hola, <?php echo htmlspecialchars($nombreUsuario); ?> 👋</h1>
                <p>Bienvenido al Panel de Control de Liberty Express.</p>
            </section>

            <div class="dashboard-grid" style="padding-bottom: 1rem; gap: 1rem;">
                <div class="quick-actions">
                    <a href="/liberty/paquetes/gestion.php" class="action-btn"><i class="fas fa-box-open"></i> Registrar Paquete</a>
                    <a href="/liberty/paquetes/gestion.php" class="action-btn"><i class="fas fa-search"></i> Buscar Envío</a>
                    <a href="/liberty/paquetes/informe.php" class="action-btn"><i class="fas fa-file-alt"></i> Ver Informes</a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title"><i class="fas fa-clipboard-list"></i> Registros Hoy</h4>
                        <div class="stat-icon icon-primary"><i class="fas fa-calendar-plus"></i></div>
                    </div>
                    <h2 class="stat-value"><?php echo $ingresosHoy; ?></h2>
                    <div class="stat-footer">
                        <span class="<?php echo $claseTendenciaIng; ?>"><i class="fas <?php echo $iconoTendenciaIng; ?>"></i> <?php echo $textoTendenciaIng; ?></span> 
                        <span class="text-gray">vs ayer (<?php echo $ingresosAyer; ?>)</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title"><i class="fas fa-warehouse"></i> En Sede</h4>
                        <div class="stat-icon icon-blue"><i class="fas fa-box"></i></div>
                    </div>
                    <h2 class="stat-value"><?php echo $enSede; ?></h2>
                    <div class="stat-footer">
                        <span class="text-gray"><?php echo $porcentajeOcupacion; ?>% de capacidad</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title"><i class="fas fa-shipping-fast"></i> En Tránsito</h4>
                        <div class="stat-icon icon-orange"><i class="fas fa-truck-fast"></i></div>
                    </div>
                    <h2 class="stat-value"><?php echo $enRuta; ?></h2> 
                    <div class="stat-footer"><span class="text-gray">Despachos activos</span></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h4 class="stat-title"><i class="fas fa-check-double"></i> Entregas (Mes)</h4>
                        <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <h2 class="stat-value"><?php echo $entregadosMes; ?></h2>
                    <div class="stat-footer">
                        <span class="<?php echo $claseTendenciaEnt; ?>"><i class="fas <?php echo $iconoTendenciaEnt; ?>"></i> <?php echo $textoTendenciaEnt; ?></span>
                        <span class="text-gray">vs mes ant.</span>
                    </div>
                </div>

                <div class="forum-card" style="grid-column: span 12; background: #fff; border-radius: 12px; padding: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #e2e8f0;">
                    
                    <div class="card-header" style="padding: 12px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin:0; color: #500101; font-size: 1rem; font-weight: 600;"><i class="fas fa-bullhorn"></i> Últimas Novedades</h3>
                        <a href="/liberty/foro.php" style="font-size: 0.8rem; color: #64748b; text-decoration: none;">Ver chat completo &rarr;</a>
                    </div>
                    
                    <div class="card-body" style="padding: 0;">
                        <div id="miniChatFeed" class="dashboard-chat-list" style="padding: 0; max-height: 250px; overflow-y: auto;">
                            <?php if(empty($mensajesDash)): ?>
                                <p style="color: #94a3b8; font-style: italic; text-align: center; padding: 20px; font-size: 0.9rem;">No hay novedades recientes.</p>
                            <?php else: ?>
                                <?php foreach($mensajesDash as $msg): ?>
                                    <div style="padding: 10px 20px; border-bottom: 1px solid #f8fafc; display: flex; gap: 12px; align-items: flex-start;">
                                        <div style="width: 32px; height: 32px; background: #e2e8f0; color: #500101; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 8px; flex-shrink: 0; font-size: 0.8rem; margin-top: 2px;">
                                            <?php echo strtoupper(substr($msg->nombre, 0, 1)); ?>
                                        </div>
                                        <div style="flex-grow: 1; min-width: 0;">
                                            <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 2px;">
                                                <span style="font-weight: 700; font-size: 0.9rem; color: #1e293b;"><?php echo htmlspecialchars($msg->nombre); ?></span>
                                                <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo haceCuantoDash($msg->fecha); ?></span>
                                            </div>
                                            <div style="font-size: 0.9rem; color: #334155; line-height: 1.4; word-wrap: break-word;">
                                                <?php echo nl2br(htmlspecialchars($msg->mensaje)); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div style="padding: 10px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <form action="/liberty/app/db/functions/foro/publicar.php" method="POST" style="display: flex; gap: 10px;">
                                <input type="hidden" name="origen" value="dashboard">
                                <input type="text" name="mensaje" placeholder="Escribe una novedad rápida..." style="flex-grow: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem;" required autocomplete="off">
                                <button class="btn-post" style="background: #500101; color: white; border: none; width: 36px; height: 36px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-paper-plane" style="font-size: 0.9rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
    <script>
        // Auto-scroll al fondo del mini chat
        document.addEventListener("DOMContentLoaded", function() {
            var chatBox = document.getElementById("miniChatFeed");
            if(chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    </script>
</body>
</html>