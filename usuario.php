<?php
session_start();

// 1. Comprobar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

// 2. Incluir la conexión y el NUEVO archivo de lógica (para las funciones)
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/users/users.php'); // Incluimos el archivo de lógica para las funciones

// 3. Obtener todos los usuarios
try {
    $stmt = $conn->query("SELECT * FROM usuario ORDER BY id ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "Error al obtener usuarios: " . $e->getMessage();
    die();
}

// 4. --- NUEVO --- Obtener todos los roles disponibles para los modales
$rolesDisponibles = obtenerRoles(); // Usamos la nueva función de users.php

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Dashboard</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/users.css">
</head>

<body>

    <?php
    // Incluye el menú lateral
    include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php');
    ?>

    <main class="main-content">

        <div class="header">
            <h1>Gestión de Usuarios</h1>
            <button class="btn btn-primary" onclick="abrirModal('modalAnadir')">
                Añadir Nuevo Usuario
            </button>
        </div>

        <?php if (isset($_SESSION['user_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['user_message'];
                unset($_SESSION['user_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['user_error'];
                unset($_SESSION['user_error']); ?>
            </div>
        <?php endif; ?>

        <div class="search-bar">
            <input type="text" id="buscador" onkeyup="buscarTabla()" placeholder="Buscar por nombre, correo, etc...">
        </div>

        <div class="table-container">
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
                            <td colspan="8" style="text-align: center;">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario->id); ?></td>
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
                                        <svg class="icon-edit" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="#333"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="#333"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <button class="btn btn-danger" onclick="abrirModalEliminar(<?php echo $usuario->id; ?>)"
                                        title="Eliminar">
                                        <svg class="icon-delete" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <div id="modalAnadir" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Añadir Nuevo Usuario</h2>
                <span class="close-btn" onclick="cerrarModal('modalAnadir')">&times;</span>
            </div>

            <form action="/liberty/app/db/functions/users/users.php" method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="anadirNombre">Nombre:</label>
                    <input type="text" id="anadirNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="anadirApellido">Apellido:</label>
                    <input type="text" id="anadirApellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="anadirCorreo">Correo:</label>
                    <input type="email" id="anadirCorreo" name="correo" required>
                </div>
                <div class="form-group">
                    <label for="anadirContraseña">Contraseña:</label>
                    <input type="password" id="anadirContraseña" name="contraseña" required>
                </div>
                <div class="form-group">
                    <label for="anadirTurno">Turno:</label>
                    <select id="anadirTurno" name="turno">
                        <option value="0" selected>Mañana</option>
                        <option value="1">Tarde</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="anadirRol">Rol:</label>
                    <select id="anadirRol" name="rol_id" required>
                        <?php foreach ($rolesDisponibles as $rol): ?>
                            <option 
                                value="<?php echo htmlspecialchars($rol['id']); ?>" 
                                <?php echo ($rol['id'] == 2) ? 'selected' : ''; // Selecciona 'Usuario' (ID 2) por defecto ?>>
                                <?php echo htmlspecialchars($rol['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="anadirEstado">Estado:</label>
                    <select id="anadirEstado" name="estado">
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary"
                        onclick="cerrarModal('modalAnadir')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Usuario</h2>
                <span class="close-btn" onclick="cerrarModal('modalEditar')">&times;</span>
            </div>

            <form action="/liberty/app/db/functions/users/users.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editarId" name="id">

                <div class="form-group">
                    <label for="editarNombre">Nombre:</label>
                    <input type="text" id="editarNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editarApellido">Apellido:</label>
                    <input type="text" id="editarApellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="editarCorreo">Correo:</label>
                    <input type="email" id="editarCorreo" name="correo" required>
                </div>
                <div class="form-group">
                    <label for="editarContraseña">Contraseña:</label>
                    <input type="password" id="editarContraseña" name="contraseña" placeholder="Dejar en blanco para no cambiar">
                </div>
                <div class="form-group">
                    <label for="editarTurno">Turno:</label>
                    <select id="editarTurno" name="turno">
                        <option value="0">Mañana</option>
                        <option value="1">Tarde</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editarRol">Rol:</label>
                    <select id="editarRol" name="rol_id" required>
                        <?php foreach ($rolesDisponibles as $rol): ?>
                            <option value="<?php echo htmlspecialchars($rol['id']); ?>">
                                <?php echo htmlspecialchars($rol['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editarEstado">Estado:</label>
                    <select id="editarEstado" name="estado">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary"
                        onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmar Eliminación</h2>
                <span class="close-btn" onclick="cerrarModal('modalEliminar')">&times;</span>
            </div>
            <p>¿Estás seguro de que deseas eliminar a este usuario? Esta acción no se puede deshacer.</p>

            <form action="/liberty/app/db/functions/users/users.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="eliminarId" name="id">

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary"
                        onclick="cerrarModal('modalEliminar')">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/liberty/app/assets/js/users.js"></script>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>