<?php
session_start();

// 1. Comprobar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// 2. Incluir la conexión y funciones
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/users/users.php');

// 3. Obtener todos los usuarios
try {
    $stmt = $conn->query("SELECT * FROM usuario ORDER BY id ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "Error al obtener usuarios: " . $e->getMessage();
    die();
}

// 4. Obtener roles para los selects
$rolesDisponibles = obtenerRoles(); 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Liberty Express</title>
    
    <link rel="stylesheet" href="/liberty/app/assets/css/users.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/forms.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="app-wrap">

        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>

        <main class="main-content">

            <?php if (isset($_SESSION['user_message'])): ?>
                <div class="mensaje exito">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['user_message']; unset($_SESSION['user_message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_error'])): ?>
                <div class="mensaje error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['user_error']; unset($_SESSION['user_error']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="header" style="margin-bottom: 1.5rem;">
                    <h1>Gestión de Usuarios</h1>
                    <button class="btn-primary" onclick="abrirModal('modalAnadir')">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
                </div>

                <div class="search-bar" style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="buscador" onkeyup="buscarTabla()" placeholder="Buscar por nombre, apellido o correo..." style="padding-left: 40px;">
                </div>

                <div class="table-responsive">
                    <table class="content-table" id="tablaUsuarios">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Correo</th>
                                <th>Turno</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 2rem;">No hay usuarios registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($usuario->id); ?></strong></td>
                                        <td><?php echo htmlspecialchars($usuario->nombre); ?></td>
                                        <td><?php echo htmlspecialchars($usuario->apellido); ?></td>
                                        <td><?php echo htmlspecialchars($usuario->correo); ?></td>
                                        <td><?php echo traducirTurno($usuario->turno); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo traducirRol($usuario->rol_id); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $usuario->estado == 1 ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo traducirEstado($usuario->estado); ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn btn-warning" onclick="abrirModalEditar(this)"
                                                data-id="<?php echo $usuario->id; ?>"
                                                data-nombre="<?php echo htmlspecialchars($usuario->nombre); ?>"
                                                data-apellido="<?php echo htmlspecialchars($usuario->apellido); ?>"
                                                data-correo="<?php echo htmlspecialchars($usuario->correo); ?>"
                                                data-turno="<?php echo $usuario->turno; ?>"
                                                data-rol="<?php echo $usuario->rol_id; ?>"
                                                data-estado="<?php echo $usuario->estado; ?>"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <a href="/liberty/app/db/functions/users/enviar_reset.php?id=<?php echo $usuario->id; ?>" 
                                                class="btn" 
                                                style="background-color: rgba(107, 33, 168, 0.1);" 
                                                title="Enviar correo de cambio de contraseña"
                                                onclick="return confirm('¿Estás seguro? Se enviará un correo a <?php echo $usuario->correo; ?> para que cambie su contraseña.');">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" stroke="#6b21a8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                            
                                            <button class="btn btn-danger" onclick="abrirModalEliminar(<?php echo $usuario->id; ?>)"
                                                title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <div class="modal-backdrop" id="modal-backdrop"></div>

    <div id="modalAnadir" class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Usuario</h2>
            <span class="modal-close-btn" onclick="cerrarModal('modalAnadir')">&times;</span>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/users/users.php" method="POST" class="form-container">
                <input type="hidden" name="action" value="create">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="anadirNombre" class="form-label">Nombre</label>
                        <input type="text" id="anadirNombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirApellido" class="form-label">Apellido</label>
                        <input type="text" id="anadirApellido" name="apellido" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anadirCorreo" class="form-label">Correo Electrónico</label>
                    <input type="email" id="anadirCorreo" name="correo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="anadirContraseña" class="form-label">Contraseña</label>
                    <input type="password" id="anadirContraseña" name="contraseña" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="anadirRol" class="form-label">Rol</label>
                        <select id="anadirRol" name="rol_id" class="form-control" required>
                            <?php foreach ($rolesDisponibles as $rol): ?>
                                <option value="<?php echo htmlspecialchars($rol['id']); ?>" <?php echo ($rol['id'] == 2) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="anadirTurno" class="form-label">Turno</label>
                        <select id="anadirTurno" name="turno" class="form-control">
                            <option value="0">Mañana</option>
                            <option value="1">Tarde</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anadirEstado" class="form-label">Estado</label>
                    <select id="anadirEstado" name="estado" class="form-control">
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModal('modalAnadir')">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal-content">
        <div class="modal-header">
            <h2>Editar Usuario</h2>
            <span class="modal-close-btn" onclick="cerrarModal('modalEditar')">&times;</span>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/users/users.php" method="POST" class="form-container">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editarId" name="id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="editarNombre" class="form-label">Nombre</label>
                        <input type="text" id="editarNombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editarApellido" class="form-label">Apellido</label>
                        <input type="text" id="editarApellido" name="apellido" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editarCorreo" class="form-label">Correo</label>
                    <input type="email" id="editarCorreo" name="correo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editarContraseña" class="form-label">Nueva Contraseña <small style="color:#666;">(Dejar vacío para no cambiar)</small></label>
                    <input type="password" id="editarContraseña" name="contraseña" class="form-control">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="editarRol" class="form-label">Rol</label>
                        <select id="editarRol" name="rol_id" class="form-control" required>
                            <?php foreach ($rolesDisponibles as $rol): ?>
                                <option value="<?php echo htmlspecialchars($rol['id']); ?>">
                                    <?php echo htmlspecialchars($rol['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editarTurno" class="form-label">Turno</label>
                        <select id="editarTurno" name="turno" class="form-control">
                            <option value="0">Mañana</option>
                            <option value="1">Tarde</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editarEstado" class="form-label">Estado</label>
                    <select id="editarEstado" name="estado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-submit">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEliminar" class="modal-content">
        <div class="modal-header">
            <h2>Confirmar Eliminación</h2>
            <span class="modal-close-btn" onclick="cerrarModal('modalEliminar')">&times;</span>
        </div>
        <div class="modal-body">
            <div style="text-align: center; padding: 10px;">
                 <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;"></i>
                 <p style="font-size: 1.1rem;">¿Realmente deseas eliminar este usuario?</p>
                 <p style="color: #64748b; font-size: 0.9rem;">Esta acción no se puede deshacer.</p>
            </div>
            <form action="/liberty/app/db/functions/users/users.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="eliminarId" name="id">
                
                <div class="form-actions" style="justify-content: center; gap: 15px;">
                    <button type="button" class="btn-secondary" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                    <button type="submit" class="btn-submit" style="background-color: #dc2626;">Sí, Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/liberty/app/assets/js/users.js"></script>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>