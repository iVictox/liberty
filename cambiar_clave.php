<?php
session_start();
// Seguridad: Si no ha iniciado sesión (paso previo del login), lo sacamos.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña Requerido</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/style.css">
    <style>
        /* Estilos específicos para esta alerta */
        .info-msg {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
            padding: 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="/liberty/app/db/functions/login/procesar_cambio_clave.php" method="post">
            <div class="login-img">
                <img src="/liberty/app/assets/img/liberty_express_europa_logo.jpeg" alt="Logo" />
            </div>
            
            <h2>Seguridad de Cuenta</h2>
            
            <div class="info-msg">
                <strong>¡Bienvenido!</strong><br>
                Por seguridad, es necesario que configures una nueva contraseña personal antes de continuar.
            </div>

            <?php
            if (isset($_SESSION['cambio_error'])) {
                echo '<div class="error-msg">' . htmlspecialchars($_SESSION['cambio_error']) . '</div>';
                unset($_SESSION['cambio_error']);
            }
            ?>

            <div class="form-group">
                <label for="p_nueva">Nueva Contraseña</label>
                <div class="password-box">
                    <input type="password" id="p_nueva" name="p_nueva" required placeholder="Mínimo 6 caracteres">
                </div>
            </div>

            <div class="form-group">
                <label for="p_confirmar">Confirmar Nueva Contraseña</label>
                <div class="password-box">
                    <input type="password" id="p_confirmar" name="p_confirmar" required placeholder="Repite la contraseña">
                </div>
            </div>

            <button type="submit" class="login-btn">Guardar y Continuar</button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="/liberty/cerrar.php" style="color: #666; text-decoration: none; font-size: 13px;">Cancelar y Cerrar Sesión</a>
            </div>
        </form>
    </div>
    
    <script src="/liberty/app/assets/js/login.js"></script>
</body>
</html>