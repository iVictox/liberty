<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// --- LÓGICA DE BÚSQUEDA (BACKEND) ---
// Detectamos si hay una búsqueda activa (ya sea por carga normal o AJAX)
$buscar = $_GET['buscar'] ?? '';
$es_ajax = isset($_GET['ajax_mode']); // Bandera para saber si pide solo filas

$sql = "
    SELECT 
        p.Codigo, 
        p.Fecha_Registro, 
        p.Status,
        o.Nombre AS OrigenNombre,
        d.Nombre AS DestinoNombre,
        d.Modalidad AS DestinoModalidad
    FROM 
        Paquete p
    JOIN 
        Origen o ON p.Origen_id = o.Origen_id
    JOIN 
        Destino d ON p.Destino_id = d.Destino_id
    JOIN 
        usuario u ON p.Usuario_id = u.id
";

$params = [];
if (!empty($buscar)) {
    $sql .= "
    WHERE 
        p.Codigo LIKE ? OR 
        o.Nombre LIKE ? OR 
        d.Nombre LIKE ? OR
        p.Status LIKE ? OR
        d.Modalidad LIKE ? 
    ";
    $like_term = '%' . $buscar . '%';
    $params = [$like_term, $like_term, $like_term, $like_term, $like_term];
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

// --- SI ES AJAX (BÚSQUEDA EN TIEMPO REAL) ---
// Si Javascript llama a este archivo con ?ajax_mode=1, devolvemos SOLO las filas y cortamos.
if ($es_ajax) {
    if (empty($paquetes)) {
        echo '<tr><td colspan="6">No hay paquetes que coincidan con "' . htmlspecialchars($buscar) . '".</td></tr>';
    } else {
        foreach ($paquetes as $paquete) {
            // Lógica de visualización (repetida para ajax)
            $status_limpio = strtolower($paquete->Status);
            $status_limpio = str_replace([' ', 'ó'], ['', 'o'], $status_limpio);
            
            echo '<tr class="filterable-row">';
            echo '<td>' . htmlspecialchars($paquete->Codigo) . '</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($paquete->Fecha_Registro)) . '</td>';
            echo '<td>' . htmlspecialchars($paquete->OrigenNombre) . '</td>';
            echo '<td>' . htmlspecialchars($paquete->DestinoNombre) . 
                 '<span style="font-size: 0.8em; color: #555; display: block;">(' . htmlspecialchars($paquete->DestinoModalidad) . ')</span></td>';
            echo '<td><span class="status-badge status-' . $status_limpio . '">' . htmlspecialchars($paquete->Status) . '</span></td>';
            echo '<td>
                    <button class="btn-action edit btn-open-editar"
                        data-codigo="' . htmlspecialchars($paquete->Codigo) . '"
                        data-status="' . htmlspecialchars($paquete->Status) . '"
                        data-modalidad="' . htmlspecialchars($paquete->DestinoModalidad) . '">
                        Editar Status
                    </button>
                    <button class="btn-action delete btn-open-eliminar"
                        data-codigo="' . htmlspecialchars($paquete->Codigo) . '">
                        Eliminar
                    </button>
                  </td>';
            echo '</tr>';
        }
    }
    exit; // IMPORTANTE: Detener la ejecución aquí para no devolver el resto del HTML
}

// --- CARGA NORMAL DE PÁGINA ---
// Obtenemos listas para los modales (solo si es carga normal)
$origenes_stmt = $conn->query("SELECT Origen_id, Nombre FROM Origen ORDER BY Nombre ASC");
$origenes = $origenes_stmt->fetchAll(PDO::FETCH_OBJ);

$destinos_stmt = $conn->query("SELECT Destino_id, Nombre, Modalidad FROM Destino WHERE Estado = 'Activo' ORDER BY Nombre ASC");
$destinos = $destinos_stmt->fetchAll(PDO::FETCH_OBJ);

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
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="table-container">
                <div class="table-header">
                    <h1>Gestión de Paquetes</h1>
                    <button id="btn-open-crear" class="btn-primary">+ Registrar paquete</button>
                </div>

                <?php if ($mensaje): ?>
                    <div class="mensaje <?php echo $mensaje['tipo']; ?>"><?php echo htmlspecialchars($mensaje['texto']); ?></div>
                <?php endif; ?>

                <div class="form-container" style="max-width: none; margin-bottom: 20px; padding: 0;">
                    <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                        <input type="text" id="input-busqueda-realtime" class="form-control" placeholder="Escribe para buscar en tiempo real..." value="<?php echo htmlspecialchars($buscar); ?>" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha Registro</th>
                                <th>Origen</th>
                                <th>Destino (Tipo)</th>
                                <th>Status</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados">
                            <?php if (empty($paquetes)): ?>
                                <tr>
                                    <td colspan="6">No hay paquetes registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paquetes as $paquete): ?>
                                    <tr class="filterable-row">
                                        <td><?php echo htmlspecialchars($paquete->Codigo); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($paquete->Fecha_Registro)); ?></td>
                                        <td><?php echo htmlspecialchars($paquete->OrigenNombre); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($paquete->DestinoNombre); ?>
                                            <span style="font-size: 0.8em; color: #555; display: block;">
                                                (<?php echo htmlspecialchars($paquete->DestinoModalidad); ?>)
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                                $status_limpio = strtolower($paquete->Status);
                                                $status_limpio = str_replace([' ', 'ó'], ['', 'o'], $status_limpio);
                                            ?>
                                            <span class="status-badge status-<?php echo $status_limpio; ?>">
                                                <?php echo htmlspecialchars($paquete->Status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-action edit btn-open-editar"
                                                data-codigo="<?php echo htmlspecialchars($paquete->Codigo); ?>"
                                                data-status="<?php echo htmlspecialchars($paquete->Status); ?>"
                                                data-modalidad="<?php echo htmlspecialchars($paquete->DestinoModalidad); ?>">
                                                Editar Status
                                            </button>
                                            <button class="btn-action delete btn-open-eliminar"
                                                data-codigo="<?php echo htmlspecialchars($paquete->Codigo); ?>">
                                                Eliminar
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

    <div class="modal-content" id="modal-crear" style="max-width: 600px;"> 
        <div class="modal-header">
            <h2>Registrar Paquetes</h2>
            <button class="modal-close-btn" data-modal-id="modal-crear">×</button>
        </div>
        <div class="modal-body">
            <form action="/liberty/app/db/functions/paquetes/crear.php" method="POST" class="form-container" id="form-crear-lote">
                <input type="hidden" name="paquetes_json" id="paquetes_json">
                <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <h3 style="font-size: 14px; color: #500101; margin-top:0;">Configuración de Ruta</h3>
                    <div class="form-row" style="display: flex; gap: 10px;">
                        <div class="form-group" style="flex: 1; margin-bottom: 10px;">
                            <label for="origen_id" class="form-label" style="font-size:12px;">Origen</label>
                            <select id="origen_id" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($origenes as $origen): ?>
                                    <option value="<?php echo $origen->Origen_id; ?>"><?php echo htmlspecialchars($origen->Nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 10px;">
                            <label for="crear-tipo-destino" class="form-label" style="font-size:12px;">Tipo</label>
                            <select id="crear-tipo-destino" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <option value="Ruta">Ruta</option>
                                <option value="Tienda">Tienda</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 5px;">
                        <label for="destino_id" class="form-label" style="font-size:12px;">Destino</label>
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

                <div style="border-top: 1px solid #eee; padding-top: 10px;">
                    <h3 style="font-size: 14px; color: #500101; margin-top:0;">Agregar Paquetes</h3>
                    <div class="form-group" style="display: flex; gap: 10px; align-items: flex-end;">
                        <div style="flex-grow: 1;">
                            <label for="codigo" class="form-label">Código de Paquete (Tracking)</label>
                            <input type="text" id="codigo" class="form-control" placeholder="Ej: TRK-12345XYZ">
                        </div>
                        <button type="button" id="btn-agregar-lista" class="btn-submit" style="background-color: #28a745; padding: 12px 15px;">+ Agregar</button>
                    </div>
                    <div class="lista-lote-container">
                        <table class="data-table" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Ruta (Origen -> Destino)</th>
                                    <th style="width: 30px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-lista-lote">
                                <tr id="lista-vacia-msg">
                                    <td colspan="3" style="text-align: center; color: #999;">No hay paquetes en el lote aún.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-actions" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; color: #666;">Total paquetes: <strong id="contador-paquetes">0</strong></span>
                    <button type="submit" class="btn-submit">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-content" id="modal-editar">
        <div class="modal-header">
            <h2>Editar Status del Paquete</h2>
            <button class="modal-close-btn" data-modal-id="modal-editar">×</button>
        </div>
        <div class="modal-body">
            <form id="form-editar" action="" method="POST" class="form-container">
                <p>Cambiando status para el paquete: <strong id="codigo-paquete-editar"></strong></p>
                <div class="form-group">
                    <label for="editar-status" class="form-label">Nuevo Status</label>
                    <select id="editar-status" name="status" class="form-control" required></select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Actualizar Status</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-content" id="modal-eliminar">
        <div class="modal-header">
            <h2>Confirmar Eliminación</h2>
            <button class="modal-close-btn" data-modal-id="modal-eliminar">×</button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro de que desea eliminar el paquete <strong id="codigo-paquete-eliminar"></strong>?</p>
            <div class="form-actions" style="justify-content: flex-end;">
                <button type="button" class="btn-action modal-close-btn" data-modal-id="modal-eliminar" style="margin-right: 10px;">Cancelar</button>
                <a id="btn-confirmar-eliminar" href="#" class="btn-submit" style="background-color: #dc3545;">Sí, Eliminar</a>
            </div>
        </div>
    </div>

    <script src="/liberty/app/assets/js/sidebar.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.getElementById('modal-backdrop');
        
        // --- FUNCIONES MODAL ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) { modal.classList.add('open'); backdrop.classList.add('open'); }
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) { modal.classList.remove('open'); backdrop.classList.remove('open'); }
        }

        // Cierres globales (Botones X y Fondo)
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-close-btn')) {
                closeModal(e.target.getAttribute('data-modal-id'));
            }
            if (e.target.id === 'modal-backdrop') {
                document.querySelectorAll('.modal-content.open').forEach(modal => closeModal(modal.id));
            }
        });

        // ==============================================================
        // 1. LÓGICA DE BÚSQUEDA EN TIEMPO REAL (AJAX)
        // ==============================================================
        const inputBusqueda = document.getElementById('input-busqueda-realtime');
        const tablaResultados = document.getElementById('tabla-resultados');
        let debounceTimer;

        inputBusqueda.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const termino = this.value;

            // Esperar 300ms después de dejar de escribir para no saturar
            debounceTimer = setTimeout(() => {
                // Llamada al MISMO archivo pero con ?ajax_mode=1
                fetch(`?ajax_mode=1&buscar=${encodeURIComponent(termino)}`)
                    .then(response => response.text())
                    .then(html => {
                        tablaResultados.innerHTML = html;
                    })
                    .catch(err => console.error('Error en búsqueda:', err));
            }, 300); 
        });


        // ==============================================================
        // 2. DELEGACIÓN DE EVENTOS (PARA QUE LOS BOTONES FUNCIONEN TRAS BUSCAR)
        // ==============================================================
        // En lugar de asignar eventos a los botones directamente, escuchamos clics en toda la página
        // y verificamos si lo que se clickeó fue un botón de editar/eliminar.
        
        const modalEditar = document.getElementById('modal-editar');
        const formEditar = document.getElementById('form-editar');
        const selectStatus = document.getElementById('editar-status');
        const codigoEditarSpan = document.getElementById('codigo-paquete-editar');
        const modalEliminar = document.getElementById('modal-eliminar');
        const codigoEliminarSpan = document.getElementById('codigo-paquete-eliminar');
        const btnConfirmarEliminar = document.getElementById('btn-confirmar-eliminar');

        const statusOpciones = {
            'Ruta': ['En Sede', 'En Ruta', 'Entregado', 'Devolución'],
            'Tienda': ['En Sede', 'Transferido', 'Entregado', 'Devolución']
        };

        document.addEventListener('click', function(e) {
            // -- DETECTAR CLIC EN EDITAR --
            const btnEditar = e.target.closest('.btn-open-editar');
            if (btnEditar) {
                const codigo = btnEditar.getAttribute('data-codigo');
                const statusActual = btnEditar.getAttribute('data-status');
                const modalidad = btnEditar.getAttribute('data-modalidad');

                formEditar.action = `/liberty/app/db/functions/paquetes/editar.php?codigo=${codigo}`;
                codigoEditarSpan.textContent = codigo;
                selectStatus.innerHTML = ''; 

                let opciones = [...(statusOpciones[modalidad] || statusOpciones['Ruta'])];
                if (statusActual === 'Registrado' && !opciones.includes('Registrado')) {
                    opciones.unshift('Registrado');
                }
                
                opciones.forEach(opcion => {
                    const optionEl = document.createElement('option');
                    optionEl.value = opcion;
                    optionEl.textContent = opcion;
                    if (opcion === statusActual) optionEl.selected = true;
                    selectStatus.appendChild(optionEl);
                });
                openModal('modal-editar');
            }

            // -- DETECTAR CLIC EN ELIMINAR --
            const btnEliminar = e.target.closest('.btn-open-eliminar');
            if (btnEliminar) {
                const codigo = btnEliminar.getAttribute('data-codigo');
                codigoEliminarSpan.textContent = codigo;
                btnConfirmarEliminar.href = `/liberty/app/db/functions/paquetes/eliminar.php?codigo=${codigo}`;
                openModal('modal-eliminar');
            }
            
            // -- DETECTAR CLIC EN ABRIR CREAR (ESTÁTICO) --
            if (e.target.id === 'btn-open-crear') {
                const formCrearLote = document.getElementById('form-crear-lote');
                formCrearLote.reset();
                // Resetear variables globales del lote (ver abajo)
                lotePaquetes = []; 
                renderizarLista();
                
                const selectDestino = document.getElementById('destino_id');
                const destinoOptions = selectDestino.querySelectorAll('option');
                selectDestino.disabled = true;
                destinoOptions[0].textContent = '-- Primero seleccione un tipo --';
                for (let i = 1; i < destinoOptions.length; i++) { destinoOptions[i].style.display = 'none'; }
                
                openModal('modal-crear');
            }
        });


        // ==============================================================
        // 3. LÓGICA DE CREACIÓN LOTE Y FILTROS DESTINO (Mantenida)
        // ==============================================================
        const tipoDestinoSelect = document.getElementById('crear-tipo-destino');
        const destinoSelect = document.getElementById('destino_id');
        const destinoOptions = destinoSelect.querySelectorAll('option'); 

        tipoDestinoSelect.addEventListener('change', function() {
            const selectedTypeText = this.value.trim(); 
            destinoSelect.value = ''; 
            
            if (this.value) { 
                destinoSelect.disabled = false;
                destinoOptions[0].textContent = '-- Seleccione un destino --';
                for (let i = 1; i < destinoOptions.length; i++) { 
                    const option = destinoOptions[i];
                    const modalidad = option.getAttribute('data-modalidad');
                    if (modalidad === selectedTypeText) {
                        option.style.display = 'block'; 
                    } else {
                        option.style.display = 'none'; 
                    }
                }
            } else {
                destinoSelect.disabled = true;
                destinoOptions[0].textContent = '-- Primero seleccione un tipo --';
                for (let i = 1; i < destinoOptions.length; i++) { destinoOptions[i].style.display = 'none'; }
            }
        });

        // --- GESTIÓN DE LOTES ---
        let lotePaquetes = [];
        const btnAgregarLista = document.getElementById('btn-agregar-lista');
        const inputCodigo = document.getElementById('codigo');
        const selectOrigen = document.getElementById('origen_id');
        const inputTipoDestino = document.getElementById('crear-tipo-destino');
        //const selectDestino = document.getElementById('destino_id'); // Ya declarado
        const cuerpoLista = document.getElementById('cuerpo-lista-lote');
        const msgVacio = document.getElementById('lista-vacia-msg');
        const contadorSpan = document.getElementById('contador-paquetes');
        const inputHiddenJson = document.getElementById('paquetes_json');
        const formCrearLote = document.getElementById('form-crear-lote');

        function renderizarLista() {
            cuerpoLista.innerHTML = '';
            if (lotePaquetes.length === 0) {
                cuerpoLista.appendChild(msgVacio);
                msgVacio.style.display = 'table-row';
            } else {
                lotePaquetes.forEach((paquete, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${paquete.codigo}</strong></td>
                        <td>${paquete.origenTexto} &rarr; ${paquete.destinoTexto}</td>
                        <td><button type="button" class="btn-eliminar-item" data-index="${index}" style="color: red; background: none; border: none; font-weight: bold; cursor: pointer;">&times;</button></td>
                    `;
                    cuerpoLista.appendChild(tr);
                });
            }
            contadorSpan.textContent = lotePaquetes.length;
        }

        btnAgregarLista.addEventListener('click', function() {
            const codigo = inputCodigo.value.trim();
            const origenId = selectOrigen.value;
            const tipoDestino = inputTipoDestino.value;
            const destinoId = destinoSelect.value;

            if (!codigo) { alert("Escriba un código de paquete."); return; }
            if (!origenId) { alert("Seleccione un Origen."); return; }
            if (!tipoDestino) { alert("Seleccione Tipo de Destino."); return; }
            if (!destinoId) { alert("Seleccione un Destino."); return; }

            const existe = lotePaquetes.some(p => p.codigo === codigo);
            if (existe) { alert("Este código ya está en la lista."); return; }

            const origenTexto = selectOrigen.options[selectOrigen.selectedIndex].text;
            const destinoTexto = destinoSelect.options[destinoSelect.selectedIndex].text;

            lotePaquetes.unshift({
                codigo: codigo, origen_id: origenId, tipo_destino_varchar: tipoDestino,
                destino_id: destinoId, origenTexto: origenTexto, destinoTexto: destinoTexto
            });
            inputCodigo.value = ''; inputCodigo.focus();
            renderizarLista();
        });

        cuerpoLista.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-eliminar-item')) {
                const index = e.target.getAttribute('data-index');
                lotePaquetes.splice(index, 1);
                renderizarLista();
            }
        });

        formCrearLote.addEventListener('submit', function(e) {
            if (lotePaquetes.length === 0) {
                e.preventDefault(); alert("Debe agregar al menos un paquete.");
            } else {
                inputHiddenJson.value = JSON.stringify(lotePaquetes);
            }
        });
    });
    </script>
</body>
</html>