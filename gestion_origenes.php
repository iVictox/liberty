<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

try {
    // Obtenemos todos los campos, incluyendo Estado
    $stmt = $conn->query("SELECT * FROM Origen ORDER BY Nombre ASC");
    $origenes = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error al consultar los orígenes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Orígenes</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/tables.css"> 
    <link rel="stylesheet" href="/liberty/app/assets/css/forms.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/modal.css"> 
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        <main class="main-content">
            <div class="table-container">
                <div class="table-header">
                    <h1>Gestión de Orígenes</h1>
                    <button id="btn-open-crear" class="btn-primary">+ Registrar Nuevo</button>
                </div>

                <?php if ($mensaje): ?>
                    <div class="mensaje <?php echo $mensaje['tipo']; ?>"><?php echo htmlspecialchars($mensaje['texto']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th> <!-- Nueva Columna -->
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($origenes)): ?>
                                <tr><td colspan="4">No hay orígenes registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($origenes as $origen): ?>
                                    <tr>
                                        <td><?php echo $origen->Origen_id; ?></td>
                                        <td><?php echo htmlspecialchars($origen->Nombre); ?></td>
                                        <td>
                                            <!-- Badge simple para el estado -->
                                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #fff; background-color: <?php echo $origen->Estado == 'Activo' ? '#28a745' : '#6c757d'; ?>;">
                                                <?php echo htmlspecialchars($origen->Estado); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Pasamos data-estado al botón -->
                                            <button class="btn-action edit btn-open-editar" 
                                                    data-id="<?php echo $origen->Origen_id; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($origen->Nombre); ?>"
                                                    data-estado="<?php echo htmlspecialchars($origen->Estado); ?>">
                                                Editar
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

    <!-- MODAL DE CREAR ORIGEN -->
    <div class="modal-backdrop" id="modal-backdrop"></div>
    <div class="modal-content" id="modal-crear">
        <div class="modal-header">
            <h2>Registrar Nuevo Origen</h2>
            <button class="modal-close-btn" data-modal-id="modal-crear">×</button>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/origen/crear.php" method="POST" class="form-container">
                <div class="form-group">
                    <label for="crear-nombre" class="form-label">Nombre del Origen</label>
                    <input type="text" id="crear-nombre" name="nombre" class="form-control" placeholder="Ej: Miami" required>
                </div>
                <!-- Selector de Estado -->
                <div class="form-group">
                    <label for="crear-estado" class="form-label">Estado</label>
                    <select id="crear-estado" name="estado" class="form-control" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE EDITAR ORIGEN -->
    <div class="modal-content" id="modal-editar">
        <div class="modal-header">
            <h2>Editar Origen</h2>
            <button class="modal-close-btn" data-modal-id="modal-editar">×</button>
        </div>
        <div class="modal-body">
            <form id="form-editar" action="" method="POST" class="form-container">
                <div class="form-group">
                    <label for="editar-nombre" class="form-label">Nombre del Origen</label>
                    <input type="text" id="editar-nombre" name="nombre" class="form-control" required>
                </div>
                <!-- Selector de Estado -->
                <div class="form-group">
                    <label for="editar-estado" class="form-label">Estado</label>
                    <select id="editar-estado" name="estado" class="form-control" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript para los modales -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.getElementById('modal-backdrop');
        
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('open');
            backdrop.classList.add('open');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('open');
            backdrop.classList.remove('open');
        }

        // Abrir modal crear
        document.getElementById('btn-open-crear').addEventListener('click', () => openModal('modal-crear'));

        // Elementos del modal editar
        const formEditar = document.getElementById('form-editar');
        const inputNombre = document.getElementById('editar-nombre');
        const selectEstado = document.getElementById('editar-estado');

        // Abrir modal editar y rellenar datos
        document.querySelectorAll('.btn-open-editar').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const estado = this.getAttribute('data-estado'); // Recoger estado
                
                formEditar.action = `/liberty/app/db/functions/origen/editar.php?id=${id}`;
                inputNombre.value = nombre;
                selectEstado.value = estado; // Seleccionar el estado correcto
                
                openModal('modal-editar');
            });
        });

        // Cerrar modales con la X
        document.querySelectorAll('.modal-close-btn').forEach(button => {
            button.addEventListener('click', function() {
                closeModal(this.getAttribute('data-modal-id'));
            });
        });

        // Cerrar al hacer click fuera
        backdrop.addEventListener('click', () => {
            closeModal('modal-crear');
            closeModal('modal-editar');
        });
    });
    </script>
</body>
</html>