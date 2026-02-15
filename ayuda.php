<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ayuda y Manuales - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .help-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .help-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; transition: transform 0.2s; }
        .help-card:hover { transform: translateY(-3px); }
        .help-title { color: #500101; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .btn-download { display: inline-flex; align-items: center; gap: 8px; background: #500101; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; margin-top: 10px; }
        .btn-download:hover { background: #3a0101; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="help-container">
                <h1 style="color: #333; margin-bottom: 30px;"><i class="fas fa-question-circle"></i> Centro de Ayuda</h1>

                <div class="help-card">
                    <h2 class="help-title"><i class="fas fa-book"></i> Manual de Usuario - General</h2>
                    <p>Guía completa sobre el uso del sistema, gestión de paquetes y navegación básica.</p>
                    <a href="/liberty/app/assets/manuales/manual_general.pdf" target="_blank" class="btn-download">
                        <i class="fas fa-file-pdf"></i> Ver Manual PDF
                    </a>
                </div>

                <div class="help-card">
                    <h2 class="help-title"><i class="fas fa-truck-loading"></i> Guía de Operaciones (Almacén)</h2>
                    <p>Procedimientos para el registro de origen, destino y cambio de estatus de paquetes.</p>
                    <a href="/liberty/app/assets/manuales/manual_almacen.pdf" target="_blank" class="btn-download">
                        <i class="fas fa-file-pdf"></i> Ver Manual PDF
                    </a>
                </div>

                <?php if($_SESSION['user_rol'] == 3): // Solo Admin ?>
                <div class="help-card" style="border-left: 5px solid #500101;">
                    <h2 class="help-title"><i class="fas fa-user-shield"></i> Manual de Administrador</h2>
                    <p>Gestión de usuarios, auditoría y reportes avanzados.</p>
                    <a href="/liberty/app/assets/manuales/manual_admin.pdf" target="_blank" class="btn-download">
                        <i class="fas fa-file-pdf"></i> Ver Manual PDF
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>