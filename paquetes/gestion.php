<?php
session_start();
// --- LÓGICA DE CADUCIDAD DE SESIÓN (30 MINUTOS) ---
$timeout_duration = 1800; 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /liberty/login.php?mensaje=sesion_expirada");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
// --------------------------------------------------

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// --- LÓGICA DE BÚSQUEDA Y FILTRADO (BACKEND) ---
$buscar = $_GET['buscar'] ?? '';
$filtro_estado = $_GET['filtro_estado'] ?? 'todos'; // Nuevo filtro
$es_ajax = isset($_GET['ajax_mode']); 

// Consulta Principal
$sql = "
    SELECT 
        p.Codigo, 
        p.Fecha_Registro, 
        p.Status, 
        p.Estado,
        p.Tipo_Destino_ID,
        p.Origen_id,
        p.Destino_id,
        p.Motivo_Devolucion,
        o.Nombre AS OrigenNombre,
        d.Nombre AS DestinoNombre,
        d.Modalidad AS DestinoModalidad,
        u.nombre AS UserNombre,
        u.apellido AS UserApellido
    FROM 
        Paquete p
    JOIN 
        Origen o ON p.Origen_id = o.Origen_id
    JOIN 
        Destino d ON p.Destino_id = d.Destino_id
    JOIN 
        usuario u ON p.Usuario_id = u.id
    WHERE 1=1
";

$params = [];

// Aplicar Filtro de Estado
if ($filtro_estado === 'activo') {
    $sql .= " AND p.Estado = 1";
} elseif ($filtro_estado === 'inactivo') {
    $sql .= " AND p.Estado = 0";
}

// Aplicar Búsqueda
if (!empty($buscar)) {
    $sql .= "
    AND (p.Codigo LIKE ? OR 
        o.Nombre LIKE ? OR 
        d.Nombre LIKE ? OR
        p.Status LIKE ? OR
        u.nombre LIKE ? OR
        u.apellido LIKE ?)
    ";
    $like_term = '%' . $buscar . '%';
    // Se repite 6 veces el parámetro
    $params = array_merge($params, [$like_term, $like_term, $like_term, $like_term, $like_term, $like_term]);
}

$sql .= " ORDER BY p.Fecha_Registro DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $paquetes = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    if ($es_ajax) die("Error");
    die("Error al consultar datos: " . $e->getMessage());
}

// --- SI ES AJAX (Devolver solo filas) ---
if ($es_ajax) {
    if (empty($paquetes)) {
        echo '<tr><td colspan="7" style="text-align:center; padding: 20px;">No se encontraron resultados.</td></tr>';
    } else {
        foreach ($paquetes as $paquete) {
            $filaStyle = ($paquete->Estado == 0) ? 'opacity: 0.6; background-color: #f1f5f9;' : '';
            $status_limpio = strtolower(str_replace([' ', 'ó'], ['', 'o'], $paquete->Status));
            
            echo '<tr class="filterable-row" style="' . $filaStyle . '">';
            echo '<td><strong>' . htmlspecialchars($paquete->Codigo) . '</strong>';
            if($paquete->Estado == 0) echo '<br><span style="font-size:0.7em; color:red;">(Inactivo)</span>';
            echo '</td>';
            echo '<td>' . date('d/m/Y', strtotime($paquete->Fecha_Registro)) . '<br><small>' . date('H:i', strtotime($paquete->Fecha_Registro)) . '</small></td>';
            echo '<td>' . htmlspecialchars($paquete->UserNombre . ' ' . $paquete->UserApellido) . '</td>';
            echo '<td>' . htmlspecialchars($paquete->OrigenNombre) . '</td>';
            echo '<td>' . htmlspecialchars($paquete->DestinoNombre) . '<div style="font-size: 0.75em; color: #64748b;">' . htmlspecialchars($paquete->DestinoModalidad) . '</div></td>';
            echo '<td><span class="status-badge status-' . $status_limpio . '">' . htmlspecialchars($paquete->Status) . '</span>';
            if($paquete->Status == 'Devolución' && !empty($paquete->Motivo_Devolucion)){
                 echo '<div style="font-size:0.7rem; color:#dc2626; max-width:150px; margin-top:5px;">Reason: '.htmlspecialchars($paquete->Motivo_Devolucion).'</div>';
            }
            echo '</td>';
            echo '<td>
                    <button class="btn-action edit btn-open-editar"
                        data-codigo="' . htmlspecialchars($paquete->Codigo) . '"
                        data-origen="' . $paquete->Origen_id . '"
                        data-tipo="' . htmlspecialchars($paquete->Tipo_Destino_ID) . '"
                        data-destino="' . $paquete->Destino_id . '"
                        data-status="' . htmlspecialchars($paquete->Status) . '"
                        data-estado="' . $paquete->Estado . '"
                        data-motivo="' . htmlspecialchars($paquete->Motivo_Devolucion ?? '') . '"
                        title="Editar Paquete Completo">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                  </td>';
            echo '</tr>';
        }
    }
    exit; 
}

// --- CARGA NORMAL ---
$origenes = $conn->query("SELECT Origen_id, Nombre FROM Origen ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_OBJ);
$destinos = $conn->query("SELECT Destino_id, Nombre, Modalidad FROM Destino WHERE Estado = 'Activo' ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_OBJ);

$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Paquetes - Liberty Express</title>
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
                    <h1><i class="fas fa-boxes"></i> Gestión de Paquetes</h1>
                    <button id="btn-open-crear" class="btn-primary">
                        <i class="fas fa-plus"></i> Registrar Paquete
                    </button>
                </div>

                <div class="filters-container" style="display: flex; gap: 10px; margin-bottom: 1.5rem;">
                    <div style="position: relative; flex-grow: 1;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="input-busqueda-realtime" class="form-control" 
                               style="padding-left: 40px;" 
                               placeholder="Buscar por código, usuario, origen..." 
                               value="<?php echo htmlspecialchars($buscar); ?>" autocomplete="off">
                    </div>
                    <div>
                        <select id="select-filtro-estado" class="form-control" style="height: 100%;">
                            <option value="todos" <?php echo $filtro_estado=='todos'?'selected':''; ?>>Todos</option>
                            <option value="activo" <?php echo $filtro_estado=='activo'?'selected':''; ?>>Activos</option>
                            <option value="inactivo" <?php echo $filtro_estado=='inactivo'?'selected':''; ?>>Inactivos</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Registrado Por</th> <th>Origen</th>
                                <th>Destino</th>
                                <th>Status</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados">
                            <?php if (empty($paquetes)): ?>
                                <tr><td colspan="7" style="text-align:center; padding: 20px;">No hay paquetes registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($paquetes as $paquete): ?>
                                    <?php 
                                        $filaStyle = ($paquete->Estado == 0) ? 'opacity: 0.6; background-color: #f1f5f9;' : '';
                                        $status_limpio = strtolower(str_replace([' ', 'ó'], ['', 'o'], $paquete->Status));
                                    ?>
                                    <tr class="filterable-row" style="<?php echo $filaStyle; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($paquete->Codigo); ?></strong>
                                            <?php if($paquete->Estado == 0): ?>
                                                <br><span style="font-size:0.7em; color:red;">(Inactivo)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($paquete->Fecha_Registro)); ?><br>
                                            <small><?php echo date('H:i', strtotime($paquete->Fecha_Registro)); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($paquete->UserNombre . ' ' . $paquete->UserApellido); ?></td>
                                        
                                        <td><?php echo htmlspecialchars($paquete->OrigenNombre); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($paquete->DestinoNombre); ?>
                                            <div style="font-size: 0.75em; color: #64748b;">
                                                <?php echo htmlspecialchars($paquete->DestinoModalidad); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $status_limpio; ?>">
                                                <?php echo htmlspecialchars($paquete->Status); ?>
                                            </span>
                                            <?php if($paquete->Status == 'Devolución' && !empty($paquete->Motivo_Devolucion)): ?>
                                                <div style="font-size:0.7rem; color:#dc2626; max-width:150px; margin-top:5px; line-height:1.2;">
                                                    <i class="fas fa-comment-dots"></i> <?php echo htmlspecialchars($paquete->Motivo_Devolucion); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn-action edit btn-open-editar"
                                                data-codigo="<?php echo htmlspecialchars($paquete->Codigo); ?>"
                                                data-origen="<?php echo $paquete->Origen_id; ?>"
                                                data-tipo="<?php echo htmlspecialchars($paquete->Tipo_Destino_ID); ?>"
                                                data-destino="<?php echo $paquete->Destino_id; ?>"
                                                data-status="<?php echo htmlspecialchars($paquete->Status); ?>"
                                                data-estado="<?php echo $paquete->Estado; ?>"
                                                data-motivo="<?php echo htmlspecialchars($paquete->Motivo_Devolucion ?? ''); ?>"
                                                title="Editar Paquete Completo">
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
            <h2><i class="fas fa-box-open"></i> Registrar Paquetes</h2>
            <button class="modal-close-btn" data-modal-id="modal-crear">×</button>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/paquetes/crear.php" method="POST" class="form-container" id="form-crear-lote">
                <input type="hidden" name="paquetes_json" id="paquetes_json">
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 0.9rem; color: #500101; margin-top:0;"><i class="fas fa-map-signs"></i> 1. Configuración de Ruta</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin:0;">
                            <label for="origen_id" class="form-label">Origen</label>
                            <select id="origen_id" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($origenes as $origen): ?>
                                    <option value="<?php echo $origen->Origen_id; ?>"><?php echo htmlspecialchars($origen->Nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label for="crear-tipo-destino" class="form-label">Tipo</label>
                            <select id="crear-tipo-destino" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <option value="Ruta">Ruta</option>
                                <option value="Tienda">Tienda</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                        <label for="destino_id" class="form-label">Destino Final</label>
                        <select id="destino_id" class="form-control" disabled>
                            <option value="">-- Primero seleccione tipo --</option>
                            <?php foreach ($destinos as $destino): ?>
                                <option value="<?php echo $destino->Destino_id; ?>" data-modalidad="<?php echo htmlspecialchars($destino->Modalidad); ?>" style="display: none;">
                                    <?php echo htmlspecialchars($destino->Nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <h3 style="font-size: 0.9rem; color: #500101; margin-top:0;"><i class="fas fa-barcode"></i> 2. Escanear Códigos</h3>
                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <div style="flex-grow: 1;">
                            <input type="text" id="codigo" class="form-control" placeholder="Ej: TRK-12345XYZ" style="font-family: monospace; text-transform: uppercase;">
                        </div>
                        <button type="button" id="btn-agregar-lista" class="btn-submit"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="lista-lote-container" style="border: 1px solid #e2e8f0; border-radius: 8px; max-height: 150px; overflow-y: auto;">
                        <table class="data-table" style="margin: 0;">
                            <tbody id="cuerpo-lista-lote">
                                <tr id="lista-vacia-msg"><td colspan="3" style="text-align: center; color: #94a3b8; padding: 15px;">Lista vacía.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-actions" style="justify-content: space-between; align-items: center; margin-top: 15px;">
                    <span style="font-size: 0.85rem; color: #64748b;">Total: <strong id="contador-paquetes">0</strong></span>
                    <button type="submit" class="btn-submit">Guardar Todo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-content" id="modal-editar">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Editar Paquete</h2>
            <button class="modal-close-btn" data-modal-id="modal-editar">×</button>
        </div>
        <div class="modal-body">
            <form id="form-editar" action="" method="POST" class="form-container">
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1rem;">
                    Editando Código: <strong id="codigo-paquete-editar" style="color: #000;"></strong>
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="editar-origen" class="form-label">Origen</label>
                        <select id="editar-origen" name="origen_id" class="form-control" required>
                            <?php foreach ($origenes as $origen): ?>
                                <option value="<?php echo $origen->Origen_id; ?>"><?php echo htmlspecialchars($origen->Nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editar-tipo-destino" class="form-label">Tipo</label>
                        <select id="editar-tipo-destino" name="tipo_destino" class="form-control" required>
                            <option value="Ruta">Ruta</option>
                            <option value="Tienda">Tienda</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editar-destino" class="form-label">Destino</label>
                    <select id="editar-destino" name="destino_id" class="form-control" required>
                         <?php foreach ($destinos as $destino): ?>
                            <option value="<?php echo $destino->Destino_id; ?>" data-modalidad="<?php echo htmlspecialchars($destino->Modalidad); ?>">
                                <?php echo htmlspecialchars($destino->Nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="editar-status" class="form-label">Status Logístico</label>
                        <select id="editar-status" name="status" class="form-control" required></select>
                    </div>
                    <div class="form-group">
                        <label for="editar-estado" class="form-label">Estado Sistema</label>
                        <select id="editar-estado" name="estado" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="container-motivo-devolucion" style="display: none;">
                    <label for="editar-motivo" class="form-label" style="color: #dc2626;">Motivo de Devolución</label>
                    <textarea id="editar-motivo" name="motivo_devolucion" class="form-control" rows="2" placeholder="Explique por qué se devuelve el paquete..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary modal-close-btn" data-modal-id="modal-editar" style="margin-right: auto;">Cancelar</button>
                    <button type="submit" class="btn-submit">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/liberty/app/assets/js/sidebar.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... (Lógica de modales existente) ...
        const backdrop = document.getElementById('modal-backdrop');
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) { modal.classList.add('open'); backdrop.classList.add('open'); }
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) { modal.classList.remove('open'); backdrop.classList.remove('open'); }
        }
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-close-btn')) {
                if(e.target.type === 'button') e.preventDefault();
                closeModal(e.target.getAttribute('data-modal-id'));
            }
            if (e.target.id === 'modal-backdrop') {
                document.querySelectorAll('.modal-content.open').forEach(modal => closeModal(modal.id));
            }
        });

        // --- BÚSQUEDA Y FILTRADO AJAX ---
        const inputBusqueda = document.getElementById('input-busqueda-realtime');
        const selectFiltro = document.getElementById('select-filtro-estado');
        const tablaResultados = document.getElementById('tabla-resultados');
        let debounceTimer;

        function realizarBusqueda() {
            const termino = inputBusqueda.value;
            const filtro = selectFiltro.value;
            
            fetch(`?ajax_mode=1&buscar=${encodeURIComponent(termino)}&filtro_estado=${filtro}`)
                .then(response => response.text())
                .then(html => { tablaResultados.innerHTML = html; })
                .catch(err => console.error('Error:', err));
        }

        inputBusqueda.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(realizarBusqueda, 300); 
        });

        selectFiltro.addEventListener('change', realizarBusqueda);

        // --- LÓGICA DE EDICIÓN ---
        const formEditar = document.getElementById('form-editar');
        const codigoSpan = document.getElementById('codigo-paquete-editar');
        const editOrigen = document.getElementById('editar-origen');
        const editTipo = document.getElementById('editar-tipo-destino');
        const editDestino = document.getElementById('editar-destino');
        const editStatus = document.getElementById('editar-status');
        const editEstado = document.getElementById('editar-estado');
        const editMotivo = document.getElementById('editar-motivo');
        const containerMotivo = document.getElementById('container-motivo-devolucion');
        const editDestinoOptions = editDestino.querySelectorAll('option');

        const statusOpciones = {
            'Ruta': ['En Sede', 'En Ruta', 'Entregado', 'Devolución'],
            'Tienda': ['En Sede', 'Transferido', 'Entregado', 'Devolución']
        };

        function filtrarDestinosEdicion(tipoSeleccionado, valorDestinoActual = null) {
            let primeroVisible = null;
            let encontradoActual = false;
            for (let i = 0; i < editDestinoOptions.length; i++) {
                const option = editDestinoOptions[i];
                if (option.getAttribute('data-modalidad') === tipoSeleccionado) {
                    option.style.display = 'block';
                    if (!primeroVisible) primeroVisible = option.value;
                    if (valorDestinoActual && option.value == valorDestinoActual) encontradoActual = true;
                } else {
                    option.style.display = 'none';
                }
            }
            editDestino.value = encontradoActual ? valorDestinoActual : (primeroVisible || "");
        }

        editTipo.addEventListener('change', function() {
            filtrarDestinosEdicion(this.value);
            llenarStatusEdicion(this.value, editStatus.value);
        });

        // Mostrar/Ocultar comentario de devolución
        editStatus.addEventListener('change', function() {
            if (this.value === 'Devolución') {
                containerMotivo.style.display = 'block';
                editMotivo.setAttribute('required', 'required');
            } else {
                containerMotivo.style.display = 'none';
                editMotivo.removeAttribute('required');
                editMotivo.value = ''; // Limpiar si cambia de status
            }
        });

        function llenarStatusEdicion(tipo, statusActual) {
            editStatus.innerHTML = '';
            let opciones = [...(statusOpciones[tipo] || statusOpciones['Ruta'])];
            if (statusActual && !opciones.includes(statusActual) && !['En Sede', 'En Ruta', 'Transferido', 'Entregado', 'Devolución'].includes(statusActual)) {
                opciones.unshift(statusActual);
            }
            opciones.forEach(opcion => {
                const optionEl = document.createElement('option');
                optionEl.value = opcion;
                optionEl.textContent = opcion;
                if (opcion === statusActual) optionEl.selected = true;
                editStatus.appendChild(optionEl);
            });

            // Trigger manual para mostrar/ocultar motivo al cargar
            if (statusActual === 'Devolución') {
                containerMotivo.style.display = 'block';
            } else {
                containerMotivo.style.display = 'none';
            }
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-open-editar');
            if (btn) {
                const codigo = btn.getAttribute('data-codigo');
                const origen = btn.getAttribute('data-origen');
                const tipo = btn.getAttribute('data-tipo');
                const destino = btn.getAttribute('data-destino');
                const status = btn.getAttribute('data-status');
                const estado = btn.getAttribute('data-estado');
                const motivo = btn.getAttribute('data-motivo');

                formEditar.action = `/liberty/app/db/functions/paquetes/editar.php?codigo=${codigo}`;
                codigoSpan.textContent = codigo;
                editOrigen.value = origen;
                editTipo.value = tipo;
                editEstado.value = estado;
                editMotivo.value = motivo || '';

                filtrarDestinosEdicion(tipo, destino);
                llenarStatusEdicion(tipo, status);
                openModal('modal-editar');
            }
        });

        // ... (Lógica de Crear Paquetes se mantiene igual) ...
        const createTipo = document.getElementById('crear-tipo-destino');
        const createDestino = document.getElementById('destino_id');
        const createDestinoOpts = createDestino.querySelectorAll('option');

        createTipo.addEventListener('change', function() {
            const tipo = this.value;
            createDestino.value = '';
            if (tipo) {
                createDestino.disabled = false;
                createDestinoOpts[0].textContent = '-- Seleccione --';
                for (let i = 1; i < createDestinoOpts.length; i++) {
                    const opt = createDestinoOpts[i];
                    opt.style.display = (opt.getAttribute('data-modalidad') === tipo) ? 'block' : 'none';
                }
            } else {
                createDestino.disabled = true;
                createDestinoOpts[0].textContent = '-- Primero seleccione tipo --';
                for (let i = 1; i < createDestinoOpts.length; i++) { createDestinoOpts[i].style.display = 'none'; }
            }
        });

        document.addEventListener('click', function(e) {
            if(e.target.id === 'btn-open-crear' || e.target.closest('#btn-open-crear')) {
                 document.getElementById('form-crear-lote').reset();
                 lotePaquetes = []; renderizarLista();
                 createDestino.disabled = true;
                 openModal('modal-crear');
            }
        });

        let lotePaquetes = [];
        const btnAddList = document.getElementById('btn-agregar-lista');
        const inCodigo = document.getElementById('codigo');
        const selOrigen = document.getElementById('origen_id');
        const bodyList = document.getElementById('cuerpo-lista-lote');
        const countSpan = document.getElementById('contador-paquetes');

        function renderizarLista() {
            bodyList.innerHTML = '';
            if (lotePaquetes.length === 0) {
                bodyList.innerHTML = '<tr id="lista-vacia-msg"><td colspan="3" style="text-align: center; color: #94a3b8; padding: 15px;">Lista vacía.</td></tr>';
            } else {
                lotePaquetes.forEach((p, idx) => {
                    bodyList.innerHTML += `<tr>
                        <td style="padding:8px; font-family:monospace;">${p.codigo}</td>
                        <td style="padding:8px; font-size:0.8rem;">${p.origenTexto} &rarr; ${p.destinoTexto}</td>
                        <td style="padding:8px;"><button type="button" class="btn-eliminar-item" data-index="${idx}" style="color:#ef4444;border:none;background:none;cursor:pointer;"><i class="fas fa-times"></i></button></td>
                    </tr>`;
                });
            }
            countSpan.textContent = lotePaquetes.length;
        }

        btnAddList.addEventListener('click', function() {
            const cod = inCodigo.value.trim();
            const org = selOrigen.value;
            const tip = createTipo.value;
            const des = createDestino.value;
            if(!cod || !org || !tip || !des) { alert("Complete todos los campos de ruta y código."); return; }
            if(lotePaquetes.some(p => p.codigo === cod)) { alert("Código ya en lista."); return; }
            lotePaquetes.unshift({
                codigo: cod, origen_id: org, tipo_destino_varchar: tip, destino_id: des,
                origenTexto: selOrigen.options[selOrigen.selectedIndex].text,
                destinoTexto: createDestino.options[createDestino.selectedIndex].text
            });
            inCodigo.value = ''; inCodigo.focus();
            renderizarLista();
        });

        bodyList.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-eliminar-item');
            if(btn) { lotePaquetes.splice(btn.getAttribute('data-index'), 1); renderizarLista(); }
        });

        document.getElementById('form-crear-lote').addEventListener('submit', function(e) {
            if(lotePaquetes.length === 0) { e.preventDefault(); alert("Agregue al menos un paquete."); }
            else { document.getElementById('paquetes_json').value = JSON.stringify(lotePaquetes); }
        });
    });
    </script>
</body>
</html>