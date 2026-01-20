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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        <main class="main-content">

            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $mensaje['tipo']; ?>">
                    <?php echo htmlspecialchars($mensaje['texto']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h1>Destinos</h1>
                    <button id="btn-open-crear" class="btn-primary"><i class="fas fa-plus"></i> Nuevo Destino</button>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Tipo (Modalidad)</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($destinos)): ?>
                                <tr><td colspan="5" style="text-align:center;">No hay destinos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($destinos as $destino): ?>
                                    <tr>
                                        <td><strong><?php echo $destino->Destino_id; ?></strong></td>
                                        <td><?php echo htmlspecialchars($destino->Nombre); ?></td>
                                        <td><span class="status-badge" style="background:#f1f5f9; color:#475569;"><?php echo htmlspecialchars($destino->Modalidad); ?></span></td>
                                        <td>
                                            <span class="status-badge <?php echo ($destino->Estado == 'Activo') ? 'status-entregado' : 'status-devolucion'; ?>">
                                                <?php echo htmlspecialchars($destino->Estado); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-action edit btn-open-editar" 
                                                data-id="<?php echo $destino->Destino_id; ?>"
                                                data-nombre="<?php echo htmlspecialchars($destino->Nombre); ?>"
                                                data-modalidad="<?php echo htmlspecialchars($destino->Modalidad); ?>"
                                                data-estado="<?php echo htmlspecialchars($destino->Estado); ?>"
                                                title="Editar">
                                                <i class="fas fa-edit"></i> Editar
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

    <div class="modal-content" id="modal-crear">
        <div class="modal-header">
            <h2>Nuevo Destino</h2>
            <button class="modal-close-btn" data-modal-id="modal-crear">×</button>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/destino/crear.php" method="POST" class="form-container">
                <div class="form-group">
                    <label for="crear-nombre" class="form-label">Nombre</label>
                    <input type="text" id="crear-nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="crear-modalidad" class="form-label">Modalidad</label>
                    <select id="crear-modalidad" name="modalidad" class="form-control">
                        <option value="Ruta">Ruta</option>
                        <option value="Tienda">Tienda</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="crear-estado" class="form-label">Estado</label>
                    <select id="crear-estado" name="estado" class="form-control">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Guardar</button>
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
                    <label for="editar-nombre" class="form-label">Nombre</label>
                    <input type="text" id="editar-nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editar-modalidad" class="form-label">Modalidad</label>
                    <select id="editar-modalidad" name="modalidad" class="form-control">
                        <option value="Ruta">Ruta</option>
                        <option value="Tienda">Tienda</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editar-estado" class="form-label">Estado</label>
                    <select id="editar-estado" name="estado" class="form-control">
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
        function openModal(id) { document.getElementById(id).classList.add('open'); backdrop.classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); backdrop.classList.remove('open'); }

        document.getElementById('btn-open-crear').addEventListener('click', () => openModal('modal-crear'));
        
        document.querySelectorAll('.btn-open-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('form-editar').action = `/liberty/app/db/functions/destino/editar.php?id=${id}`;
                document.getElementById('editar-nombre').value = this.getAttribute('data-nombre');
                document.getElementById('editar-modalidad').value = this.getAttribute('data-modalidad');
                document.getElementById('editar-estado').value = this.getAttribute('data-estado');
                openModal('modal-editar');
            });
        });

        document.querySelectorAll('.modal-close-btn').forEach(btn => {
            btn.addEventListener('click', function() { closeModal(this.getAttribute('data-modal-id')); });
        });

        backdrop.addEventListener('click', () => {
            document.querySelectorAll('.modal-content.open').forEach(m => closeModal(m.id));
        });
    });
    </script>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>