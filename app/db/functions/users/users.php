<?php
// Inicia la sesión para manejar mensajes de feedback
if (session_status() === PHP_SESSION_NONE) {
session_start();
}
// Incluye la conexión a la base de datos
// La ruta __DIR__ viaja dos niveles arriba (fuera de 'users' y 'functions') y entra a 'connect.php'
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

/*
 * -------------------------------------------------
 * FUNCIONES DE AYUDA
 * (Definidas aquí para que usuario.php pueda usarlas)
 * -------------------------------------------------
 */

// --- FUNCIÓN MODIFICADA ---
// Ahora consulta la base de datos para obtener el nombre del rol
if (!function_exists('traducirRol')) {
    function traducirRol($rol_id)
    {
        global $conn; // Accede a la conexión definida en la línea 6

        if (!$conn) {
            // Fallback por si la conexión falla (aunque no debería)
            return ($rol_id == 1) ? 'Administrador' : 'Usuario';
        }

        try {
            $stmt = $conn->prepare("SELECT Nombre FROM rol WHERE id = ?");
            $stmt->execute([$rol_id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);

            return $rol ? $rol['Nombre'] : 'Desconocido';

        } catch (PDOException $e) {
            error_log("Error en traducirRol: " . $e->getMessage());
            return 'Error DB';
        }
    }
}

// --- NUEVA FUNCIÓN ---
// Para obtener todos los roles de la BD y usarlos en un <select>
if (!function_exists('obtenerRoles')) {
    function obtenerRoles()
    {
        global $conn;
        if (!$conn) {
            return []; // Retorna array vacío si no hay conexión
        }
        try {
            // Obtiene todos los roles de la nueva tabla
            $stmt = $conn->prepare("SELECT id, Nombre FROM rol ORDER BY Nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerRoles: " . $e->getMessage());
            return [];
        }
    }
}


if (!function_exists('traducirEstado')) {
    function traducirEstado($estado)
    {
        switch ($estado) {
            case 1: // Mantenemos tu lógica de 1 = Activo
                return 'Activo';
            case 0: // Mantenemos tu lógica de 0 = Inactivo
                return 'Inactivo';
            default:
                return 'Desconocido';
        }
    }
}

if (!function_exists('traducirTurno')) {
    function traducirTurno($turno)
    {
        switch ($turno) {
            case 0:
                return 'Mañana';
            case 1:
                return 'Tarde';
            default:
                return 'N/A';
        }
    }
}

/*
 * -------------------------------------------------
 * MANEJADOR DE ACCIONES (POST)
 * (Este bloque no necesita cambios, ya funciona con rol_id)
 * -------------------------------------------------
 */

// Verifica si se está enviando una acción por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // 1. Verificar que el usuario sea Admin ( rol 1)
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_rol'] != 3) {
        $_SESSION['user_error'] = 'Acceso denegado. No tienes permisos de administrador.';
        header('Location: /liberty/usuario.php');
        exit;
    }

    $action = $_POST['action'];

    try {
        switch ($action) {

            // --- ACCIÓN: CREAR USUARIO ---
            case 'create':
                $nombre = trim($_POST['nombre']);
                $apellido = trim($_POST['apellido']);
                $correo = trim($_POST['correo']);
                $contraseña_plana = trim($_POST['contraseña']);
                $turno = (int) $_POST['turno'];
                $rol_id = (int) $_POST['rol_id'];
                $estado = trim($_POST['estado']);

                if (empty($nombre) || empty($apellido) || empty($correo) || empty($contraseña_plana)) {
                    $_SESSION['user_error'] = 'Error: Campos básicos vacíos.';
                } else {
                    $hash_contraseña = password_hash($contraseña_plana, PASSWORD_DEFAULT);
                    
                    // MODIFICACIÓN: Insertamos '1' en requiere_cambio
                    $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, correo, contraseña, turno, rol_id, estado, requiere_cambio) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$nombre, $apellido, $correo, $hash_contraseña, $turno, $rol_id, $estado]);
                    
                    $_SESSION['user_message'] = '¡Usuario creado! Deberá cambiar su contraseña al ingresar.';
                }
                break;

            // --- ACCIÓN: EDITAR USUARIO ---
            case 'edit':
                $id = (int) $_POST['id'];
                $nombre = trim($_POST['nombre']);
                $apellido = trim($_POST['apellido']);
                $correo = trim($_POST['correo']);
                $contraseña_plana = trim($_POST['contraseña']); // Puede estar vacío
                $turno = (int) $_POST['turno'];
                $rol_id = (int) $_POST['rol_id'];
                $estado = trim($_POST['estado']); // Asumimos que recibes 1 o 0

                if (empty($nombre) || empty($apellido) || empty($correo) || empty($id)) {
                    $_SESSION['user_error'] = 'Error: Campos básicos vacíos.';
                } else {
                    if (!empty($contraseña_plana)) {
                        // Si se proporcionó contraseña, actualizarla
                        $hash_contraseña = password_hash($contraseña_plana, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, contraseña = ?, turno = ?, rol_id = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$nombre, $apellido, $correo, $hash_contraseña, $turno, $rol_id, $estado, $id]);
                    } else {
                        // Si no se proporcionó, no actualizar la contraseña
                        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, turno = ?, rol_id = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$nombre, $apellido, $correo, $turno, $rol_id, $estado, $id]);
                    }
                    $_SESSION['user_message'] = '¡Usuario actualizado exitosamente!';
                }
                break;

            // --- ACCIÓN: ELIMINAR USUARIO ---
            case 'delete':
                $id = (int) $_POST['id'];

                if (empty($id)) {
                    $_SESSION['user_error'] = 'Error: ID no proporcionado.';
                } else if ($id == $_SESSION['user_id']) {
                    $_SESSION['user_error'] = 'No puedes eliminar tu propia cuenta.';
                } else {
                    $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['user_message'] = '¡Usuario eliminado exitosamente!';
                }
                break;

            default:
                $_SESSION['user_error'] = 'Acción desconocida.';
                break;
        }

    } catch (PDOException $e) {
        // Manejar errores de base de datos
        if ($e->getCode() == '23000') { // Error de integridad (ej. correo duplicado)
            $_SESSION['user_error'] = 'Error: El correo electrónico ya existe.';
        } else {
            $_SESSION['user_error'] = 'Error de base de datos: ' . $e->getMessage();
        }
    }

    // Al finalizar cualquier acción, redirigir de vuelta a la página de gestión
    header('Location: /liberty/usuario.php');
    exit;
}

// Si el archivo no se llama por POST, no hace nada.
// Esto permite que el archivo usuario.php lo incluya
// para usar las funciones de ayuda sin ejecutar la lógica POST.
?>