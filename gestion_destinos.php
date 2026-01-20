<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

try {
    $stmt = $conn->query("SELECT * FROM Destino ORDER BY Nombre ASC");
    $destinos = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error al consultar los destinos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Destinos</title>
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
                    <h1>Gestión de Destinos</h1>
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
                                <th>Tipo de Destino</th> 
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($destinos)): ?>
                                <tr><td colspan="5">No hay destinos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($destinos as $destino): ?>
                                    <tr>
                                        <td><?php echo $destino->Destino_id; ?></td>
                                        <td><?php echo htmlspecialchars($destino->Nombre); ?></td>
                                        <td><?php echo htmlspecialchars($destino->Modalidad); ?></td>
                                        <td><?php echo htmlspecialchars($destino->Estado); ?></td>
                                        <td>
                                            <button class="btn-action edit btn-open-editar" 
                                                    data-id="<?php echo $destino->Destino_id; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($destino->Nombre); ?>"
                                                    data-modalidad="<?php echo htmlspecialchars($destino->Modalidad); ?>"
                                                    data-estado="<?php echo htmlspecialchars($destino->Estado); ?>">
                                                Editar
                                            </button>
                                            
                                            <a href="/liberty/app/db/functions/destino/eliminar.php?id=<?php echo $destino->Destino_id; ?>" 
                                               class="btn-action delete" 
                                               onclick="return confirm('¿Está seguro de que desea eliminar este destino?');">
                                                Eliminar
                                            </a>
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
    <div class="modal-content" id="modal-crear">
        <div class="modal-header">
            <h2>Registrar Nuevo Destino</h2>
            <button class="modal-close-btn" data-modal-id="modal-crear">×</button>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/destino/crear.php" method="POST" class="form-container">
                <div class="form-group">
                    <label for="crear-nombre" class="form-label">Nombre del Destino</label>
                    <input type="text" id="crear-nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="crear-modalidad" class="form-label">Tipo de Destino</label>
                    <select id="crear-modalidad" name="modalidad" class="form-control" required>
                        <option value="Ruta">Ruta</option>
                        <option value="Tienda">Tienda</option>
                    </select>
                </div>

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

    <div class="modal-content" id="modal-editar">
        <div class="modal-header">
            <h2>Editar Destino</h2>
            <button class="modal-close-btn" data-modal-id="modal-editar">×</button>
        </div>
        <div class="modal-body">
            <form id="form-editar" action="" method="POST" class="form-container">
                <div class="form-group">
                    <label for="editar-nombre" class="form-label">Nombre del Destino</label>
                    <input type="text" id="editar-nombre" name="nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="editar-modalidad" class="form-label">Tipo de Destino</label>
                    <select id="editar-modalidad" name="modalidad" class="form-control" required>
                        <option value="Ruta">Ruta</option>
                        <option value="Tienda">Tienda</option>
                    </select>
                </div>

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.getElementById('modal-backdrop');
        
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('open');
                backdrop.classList.add('open');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                backdrop.classList.remove('open');
            }
        }

        document.getElementById('btn-open-crear').addEventListener('click', function() {
            openModal('modal-crear');
        });

        const formEditar = document.getElementById('form-editar');
        const inputNombre = document.getElementById('editar-nombre');
        
        // CAMBIO 4: Se actualiza la variable para reflejar que es un select
        const selectModalidad = document.getElementById('editar-modalidad');
        const selectEstado = document.getElementById('editar-estado');

        document.querySelectorAll('.btn-open-editar').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const modalidad = this.getAttribute('data-modalidad'); // Sigue usando data-modalidad
                const estado = this.getAttribute('data-estado');

                // APUNTA AL BACKEND DE EDITAR CON EL ID CORRECTO
                formEditar.action = `/liberty/app/db/functions/destino/editar.php?id=${id}`;
                inputNombre.value = nombre;
                
                // CAMBIO 5: Se asigna el valor al select en lugar del input
                selectModalidad.value = modalidad; 
                selectEstado.value = estado;

                openModal('modal-editar');
            });
        });

        document.querySelectorAll('.modal-close-btn').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-id');
                closeModal(modalId);
            });
        });

        backdrop.addEventListener('click', function() {
            closeModal('modal-crear');
            closeModal('modal-editar');
        });
    });
    </script>
    
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>