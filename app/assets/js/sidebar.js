document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Lógica para el botón del menú móvil ---
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            // Actualizar aria-expanded para accesibilidad
            const isExpanded = sidebar.classList.contains('open');
            toggleBtn.setAttribute('aria-expanded', isExpanded);
        });
    }

    // --- 2. Lógica para el dropdown de "Paquetes" ---
    const navToggles = document.querySelectorAll('.nav-toggle');

    navToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir que el enlace '#' navegue

            const subNav = this.nextElementSibling; // El .sub-nav que sigue

            if (subNav && subNav.classList.contains('sub-nav')) {
                // Añadir/quitar clases para la animación CSS
                subNav.classList.toggle('open');
                this.classList.toggle('active'); // 'active' rota la flecha
            }
        });
    });
    
    // --- 3. Abrir automáticamente el dropdown si estamos en una página hija ---
    const activeParent = document.querySelector('.nav-toggle.active-link');
    
    if (activeParent) {
        const subNav = activeParent.nextElementSibling;
        if (subNav && subNav.classList.contains('sub-nav')) {
            subNav.classList.add('open'); // Mostrar el submenú
            activeParent.classList.add('active'); // Rotar la flecha
        }
    }

});

// --- 4. Lógica de re-dimensionamiento (Mejora de tu función anterior) ---
// El CSS (sidebar.css) ya maneja el mostrar/ocultar el botón.
// Esta función solo se asegura de cerrar el menú si se agranda la pantalla.
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth > 800 && sidebar && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        
        // También reseteamos el aria-expanded del botón
        const toggleBtn = document.querySelector('.toggle-btn');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }
});