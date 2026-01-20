<?php
// Inicia la sesión para poder leer mensajes de error
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/style.css">
    <style>
        /* Estilo simple para el mensaje de error */
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="/liberty/app/db/functions/login/login.php" method="post">
            <div class="login-img">
                <img src="/liberty/app/assets/img/liberty_express_europa_logo.jpeg" alt="Bienvenido" />
            </div>
            <h2>Sistema de Despacho Liberty Express</h2>
            <h5 class="welcome-msg">¡Bienvenido! Por favor inicia sesión.</h5>

            <?php
            // Comprueba si hay un mensaje de error en la sesión
            if (isset($_SESSION['login_error'])) {
                // Muestra el mensaje de error
                echo '<div class="error-msg">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                
                // Borra el mensaje de la sesión para que no se muestre de nuevo
                unset($_SESSION['login_error']);
            }
            ?>

            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required autocomplete="email" placeholder="usuario@ejemplo.com">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-box">
                    <input type="password" id="password" name="contraseña" required autocomplete="current-password" placeholder="Contraseña">
                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" stroke="#888" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="#888" stroke-width="2"/></svg>
                        <svg class="eye-closed" style="display:none;" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.9 4.1C18.1 2.9 15.2 2 12 2c-3.2 0-6.1.9-7.9 2.1L1 1m22 22l-2.1-2.1C18.1 21.1 15.2 22 12 22c-3.2 0-6.1-.9-7.9-2.1M12 17a5 5 0 0 1-5-5c0-1.3.5-2.5 1.4-3.4M12 7a5 5 0 0 1 5 5c0 .6-.1 1.1-.3 1.6" stroke="#888" stroke-width="2" stroke-linecap="round"/><path d="M1 1l22 22" stroke="#888" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="login-btn">Ingresar</button>
        </form>
    </div>
    
    <script src="/liberty/app/assets/js/login.js"></script>

</body>
</html>