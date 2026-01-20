<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// Incluir la conexión a la base de datos (PDO)
// $conn (objeto PDO) se define aquí
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php'); 

// Incluir funciones de usuario (si aún se necesitan aquí)
// --- CORREGIDO: Faltaba un '.' (punto) de concatenación ---
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/users/users.php');

// --- NUEVO: Incluir funciones de estadísticas del dashboard (versión PDO) ---
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/dashboard/stats.php');

// --- NUEVO: Obtener las estadísticas ---
// Estas funciones ahora reciben el objeto PDO $conn
$paquetesHoy = getPaquetesRegistradosHoy($conn);
$paquetesSede = getPaquetesEnSede($conn);

// NOTA: Con PDO, no es estrictamente necesario cerrar la conexión 
// manualmente (se cierra al final del script), pero si quisieras, 
// se hace asignando null:
// $conn = null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberty Express - Dashboard</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/usuario.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/dashboard.css">
    <!-- Asegúrate de tener Font Awesome si usas los iconos fas fa- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

    <div class="app-wrap">

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>

        <main class="main-content">

            <section class="welcome-box" aria-live="polite">
                <h1 id='welcome-title'>¡Bienvenido a Liberty Express!</h1>
                <p>Gestiona envíos, registra nuevos paquetes y consulta su estado desde el panel de control.</p>
            </section>

            <div class="container-fluid py-2">
                <div class="row dashboard-stack">

                    <!-- Tarjeta Paquetes Registrados Hoy -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Paquetes Registrados Hoy</p>
                                        <!-- MODIFICADO: Se imprime la variable PHP -->
                                        <h4 class="mb-0"><?php echo $paquetesHoy; ?></h4>
                                    </div>
                                    <div
                                        class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                            </div>
                            <hr class="dark horizontal my-0">
                            <div class="card-footer p-2 ps-3">
                                <!-- Nota: Este porcentaje aún es estático -->
                                <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+55% </span>que la
                                    semana
                                    pasada</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Paquetes en Sede -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Paquetes en Sede</p>
                                        <!-- MODIFICADO: Se imprime la variable PHP -->
                                        <h4 class="mb-0"><?php echo $paquetesSede; ?></h4>
                                    </div>
                                    <div
                                        class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                </div>
                            </div>
                            <hr class="dark horizontal my-0">
                            <div class="card-footer p-2 ps-3">
                                <!-- Nota: Este porcentaje aún es estático -->
                                <p class="mb-0 text-sm"><span class="text-danger font-weight-bolder">-2% </span>que ayer
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Foro de Usuarios -->
                    <div class="col-xl-6 col-sm-12 mb-4">
                        <div class="card forum-card">
                            <div class="card-header">
                                <h4 class="mb-0">Foro de usuarios</h4>
                                <p class="text-sm mb-0">Comparte novedades, dudas o avisos</p>
                            </div>

                            <div class="card-body">
                                <div class="posts" id="postsList">
                                    <div class="post">
                                        <div class="post-meta"><strong>Ana</strong> · <span class="time">2h</span></div>
                                        <div class="post-body">¿Alguien conoce el horario de recepción de paquetes para
                                            hoy?
                                        </div>
                                    </div>

                                    <div class="post">
                                        <div class="post-meta"><strong>Carlos</strong> · <span class="time">5h</span>
                                        </div>
                                        <div class="post-body">Recordatorio: mañana habrá corte de energía en la sede.
                                        </div>
                                    </div>
                                </div>

                                <form class="post-form" onsubmit="return false;" id="forumForm">
                                    <textarea name="content" rows="3" class="form-input"
                                        placeholder="Escribe una publicación..."></textarea>
                                    <div class="form-actions">
                                        <button class="btn-post" id="btnPost">Publicar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>

</html>