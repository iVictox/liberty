<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/users/users.php');

$id = $_SESSION['user_id'];
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_password') {
    $actual = $_POST['pass_actual'];
    $nueva = $_POST['pass_nueva'];
    $confirmar = $_POST['pass_confirmar'];

    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $mensaje = "Todos los campos son obligatorios."; $tipo_mensaje = 'error';
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas nuevas no coinciden."; $tipo_mensaje = 'error';
    } elseif (strlen($nueva) < 6) {
        $mensaje = "La contraseña nueva debe tener al menos 6 caracteres."; $tipo_mensaje = 'error';
    } else {
        $stmt = $conn->prepare("SELECT contraseña FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user && password_verify($actual, $user->contraseña)) {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
            if ($update->execute([$hash, $id])) {
                $mensaje = "Contraseña actualizada correctamente."; $tipo_mensaje = 'exito';
            } else {
                $mensaje = "Error al actualizar en BD."; $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "La contraseña actual es incorrecta."; $tipo_mensaje = 'error';
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/forms.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 700px; margin: 0 auto; }
        .profile-header { text-align: center; margin-bottom: 2rem; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .profile-avatar { width: 100px; height: 100px; background: #500101; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.exito { background: #dcfce7; color: #166534; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($usuario->nombre, 0, 1)); ?>
                    </div>
                    <h2><?php echo htmlspecialchars($usuario->nombre . ' ' . $usuario->apellido); ?></h2>
                    <div>
                        <span class="badge badge-info"><?php echo traducirRol($usuario->rol_id); ?></span>
                        <span class="badge <?php echo $usuario->estado ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $usuario->estado ? 'Cuenta Activa' : 'Cuenta Inactiva'; ?>
                        </span>
                    </div>
                </div>

                <?php if ($mensaje): ?>
                    <div class="msg <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <h3 style="margin-bottom: 15px; color: #500101;"><i class="fas fa-id-card"></i> Información Personal</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario->correo); ?>" disabled style="background-color: #f8fafc;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Turno de Trabajo</label>
                            <input type="text" class="form-control" value="<?php echo traducirTurno($usuario->turno); ?>" disabled style="background-color: #f8fafc;">
                        </div>
                    </div>
                    
                    <h3 style="margin-bottom: 15px; margin-top: 30px; color: #500101;"><i class="fas fa-lock"></i> Seguridad</h3>
                    <input type="hidden" name="action" value="update_password">
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="pass_actual" class="form-control" placeholder="Ingresa tu contraseña actual para confirmar">
                    </div>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar Nueva</label>
                            <input type="password" name="pass_confirmar" class="form-control">
                        </div>
                    </div>

                    <div class="form-actions" style="justify-content: center; margin-top: 20px;">
                        <button type="submit" class="btn-submit">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>