<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Cargar PHPMailer manualmente
require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/SMTP.php';

// Validar sesión de admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_rol'] != 3) {
    $_SESSION['user_error'] = 'Acceso no autorizado.';
    header('Location: /liberty/usuario.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['user_error'] = 'ID de usuario no proporcionado.';
    header('Location: /liberty/usuario.php');
    exit;
}

try {
    // 1. Obtener correo del usuario
    $stmt = $conn->prepare("SELECT correo, nombre FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$usuario) {
        throw new Exception("Usuario no encontrado.");
    }

    // 2. Generar Token Único y Expiración (1 Hora)
    $token = bin2hex(random_bytes(32)); // Token seguro
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // 3. Guardar en BD y CAMBIAR ESTADO A INACTIVO (0)
    // Se inhabilita la cuenta hasta que restablezca la contraseña
    $update = $conn->prepare("UPDATE usuario SET token_password = ?, token_expiracion = ?, estado = 0 WHERE id = ?");
    $update->execute([$token, $expiracion, $id]);

    // 4. Configurar PHPMailer
    $mail = new PHPMailer(true);

    // Configuración del Servidor
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'libertyexpress.system@gmail.com';
    $mail->Password   = 'loyq pexn oyif mtku'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Destinatarios
    $mail->setFrom('no-reply@libertyexpress.com', 'Soporte Liberty Express');
    $mail->addAddress($usuario->correo, $usuario->nombre);

    // Contenido
    $link = "http://localhost/liberty/restablecer.php?token=" . $token;
    
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Restablecer Contraseña - Liberty Express';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #500101;'>Solicitud de Cambio de Contraseña</h2>
            <p>Hola <strong>{$usuario->nombre}</strong>,</p>
            <p>El administrador ha solicitado un restablecimiento de contraseña para tu cuenta.</p>
            <p>Por seguridad, <strong>tu cuenta ha sido inhabilitada temporalmente</strong>.</p>
            <p>Haz clic en el siguiente botón para crear una nueva contraseña y reactivar tu cuenta (válido por 1 hora):</p>
            <p>
                <a href='$link' style='background-color: #500101; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer y Reactivar</a>
            </p>
            <p>Si no funciona el botón, copia este enlace: <br> $link</p>
        </div>
    ";

    $mail->send();

    $_SESSION['user_message'] = 'Correo enviado. La cuenta ha sido inhabilitada temporalmente hasta que se restablezca la clave.';

} catch (Exception $e) {
    $_SESSION['user_error'] = 'No se pudo enviar el correo. Error: ' . $mail->ErrorInfo;
} catch (PDOException $e) {
    $_SESSION['user_error'] = 'Error de base de datos: ' . $e->getMessage();
}

header('Location: /liberty/usuario.php');
exit;
?>