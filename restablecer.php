<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$token = $_GET['token'] ?? '';
$token_valido = false;
$mensaje_error = '';

// 1. Verificar si el token existe y no ha expirado
if ($token) {
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE token_password = ? AND token_expiracion > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $token_valido = true;
    } else {
        $mensaje_error = "El enlace es inválido o ha expirado.";
    }
} else {
    $mensaje_error = "No se proporcionó un token.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="login-img">
                <img src="/liberty/app/assets/img/liberty_express_europa_logo.jpeg" alt="Logo" />
            </div>
            <h2>Nueva Contraseña</h2>

            <?php if ($token_valido): ?>
                <form action="/liberty/app/db/functions/login/procesar_restablecimiento.php" method="post">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label for="p_nueva">Nueva Contraseña</label>
                        <div class="password-box">
                            <input type="password" id="p_nueva" name="p_nueva" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="p_confirmar">Confirmar Contraseña</label>
                        <div class="password-box">
                            <input type="password" id="p_confirmar" name="p_confirmar" required placeholder="Repite la contraseña">
                        </div>
                    </div>

                    <button type="submit" class="login-btn">Cambiar Contraseña</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px;">
                    <p><?php echo $mensaje_error; ?></p>
                    <a href="/liberty/login.php" style="color: #721c24; font-weight: bold;">Volver al Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="/liberty/app/assets/js/login.js"></script>
</body>
</html>