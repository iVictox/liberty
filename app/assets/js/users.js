// app/assets/js/users.js

document.addEventListener('DOMContentLoaded', function() {
    
    const backdrop = document.getElementById('modal-backdrop');

    // --- FUNCIONES GLOBALES PARA ABRIR/CERRAR ---
    
    window.abrirModal = function(idModal) {
        const modal = document.getElementById(idModal);
        if (modal) {
            modal.classList.add('open');
            if(backdrop) backdrop.classList.add('open');
        }
    }

    window.cerrarModal = function(idModal) {
        const modal = document.getElementById(idModal);
        if (modal) {
            modal.classList.remove('open');
            if(backdrop) backdrop.classList.remove('open');
        }
    }

    // Cerrar al hacer clic en el fondo oscuro
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            document.querySelectorAll('.modal.open, .modal-content.open').forEach(modal => {
                // Identificar si es un modal clase antigua (.modal) o nueva (.modal-content)
                // En users.php usamos IDs, así que pasamos el ID a cerrarModal
                cerrarModal(modal.id);
            });
        });
    }

    // --- LÓGICA DE EDICIÓN (Pre-rellenar datos) ---
    window.abrirModalEditar = function(boton) {
        try {
            // Rellenar el formulario
            document.getElementById('editarId').value = boton.getAttribute('data-id');
            document.getElementById('editarNombre').value = boton.getAttribute('data-nombre');
            document.getElementById('editarApellido').value = boton.getAttribute('data-apellido');
            document.getElementById('editarCorreo').value = boton.getAttribute('data-correo');
            document.getElementById('editarTurno').value = boton.getAttribute('data-turno');
            document.getElementById('editarRol').value = boton.getAttribute('data-rol');
            document.getElementById('editarEstado').value = boton.getAttribute('data-estado');

            // Limpiar contraseña
            document.getElementById('editarContraseña').value = '';

            // Abrir
            abrirModal('modalEditar');
        } catch (e) {
            console.error("Error al rellenar modal editar:", e);
        }
    }

    // --- LÓGICA DE ELIMINACIÓN ---
    window.abrirModalEliminar = function(idUsuario) {
        try {
            document.getElementById('eliminarId').value = idUsuario;
            abrirModal('modalEliminar');
        } catch (e) {
            console.error("Error al rellenar modal eliminar:", e);
        }
    }

    // --- BUSCADOR EN TABLA ---
    window.buscarTabla = function() {
        var input, filtro, tabla, tr, td, i, j, txtValue;
        input = document.getElementById("buscador");
        filtro = input.value.toUpperCase();
        tabla = document.getElementById("tablaUsuarios");
        tr = tabla.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) { // Empezar en 1 para saltar encabezado
            tr[i].style.display = "none";
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filtro) > -1) {
                        tr[i].style.display = "";
                        break;
                    }
                }
            }
        }
    }

    // Ocultar alertas automáticamente
    setTimeout(() => {
        const alertas = document.querySelectorAll('.mensaje');
        alertas.forEach(alerta => {
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 500);
        });
    }, 5000);
});