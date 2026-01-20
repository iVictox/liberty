// Espera a que todo el contenido del DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {

    // --- Funciones de Modales ---
    
    // Función global para abrir modal (para que los 'onclick' funcionen)
    window.abrirModal = function(idModal) {
        const modal = document.getElementById(idModal);
        if (modal) {
            modal.style.display = 'block';
        }
    }

    // Función global para cerrar modal
    window.cerrarModal = function(idModal) {
        const modal = document.getElementById(idModal);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Cierra el modal si se hace clic fuera del contenido
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // --- Funcionalidad Específica ---

    // Pre-rellenar modal de EDITAR
    window.abrirModalEditar = function(boton) {
        try {
            // Rellenar el formulario con los datos del botón (dataset)
            document.getElementById('editarId').value = boton.dataset.id;
            document.getElementById('editarNombre').value = boton.dataset.nombre;
            document.getElementById('editarApellido').value = boton.dataset.apellido;
            document.getElementById('editarCorreo').value = boton.dataset.correo;
            document.getElementById('editarTurno').value = boton.dataset.turno;
            document.getElementById('editarRol').value = boton.dataset.rol;
            document.getElementById('editarEstado').value = boton.dataset.estado;

            // Limpiar campo de contraseña
            document.getElementById('editarContraseña').value = '';

            // Abrir el modal
            abrirModal('modalEditar');
        } catch (e) {
            console.error("Error al rellenar el modal de edición:", e);
        }
    }

    // Pre-rellenar modal de ELIMINAR
    window.abrirModalEliminar = function(idUsuario) {
        try {
            // Rellenar el ID en el formulario oculto
            document.getElementById('eliminarId').value = idUsuario;
            
            // Abrir el modal
            abrirModal('modalEliminar');
        } catch (e) {
            console.error("Error al rellenar el modal de eliminación:", e);
        }
    }

    // --- Función de Búsqueda ---
    window.buscarTabla = function() {
        var input, filtro, tabla, tr, td, i, j, textoCelda;
        input = document.getElementById("buscador");
        filtro = input.value.toUpperCase();
        tabla = document.getElementById("tablaUsuarios");
        
        if (!tabla) return; 
        
        tr = tabla.getElementsByTagName("tr");

        // Recorre todas las filas del cuerpo de la tabla (tbody)
        for (i = 0; i < tr.length; i++) {
            // Ignorar la fila de cabecera (thead)
            if (tr[i].getElementsByTagName("th").length > 0) continue;

            tr[i].style.display = "none"; // Ocultar fila por defecto
            
            td = tr[i].getElementsByTagName("td");
            
            for (j = 0; j < td.length; j++) {
                // No buscar en la última celda (la de acciones)
                if (td[j] && j < (td.length - 1)) { 
                    textoCelda = td[j].textContent || td[j].innerText;
                    if (textoCelda.toUpperCase().indexOf(filtro) > -1) {
                        tr[i].style.display = ""; 
                        break; 
                    }
                }
            }
        }
    }

    // Cerrar alertas de mensajes después de 5 segundos
    setTimeout(() => {
        const alertas = document.querySelectorAll('.alert');
        alertas.forEach(alerta => {
            alerta.style.transition = 'opacity 0.5s ease';
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 500);
        });
    }, 5000);

});