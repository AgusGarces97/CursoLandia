// Esperamos a que todo el HTML cargue antes de ejecutar el script
document.addEventListener("DOMContentLoaded", () => {
    
    // 1. NAVEGACIÓN (Navbar Active Links)
    const navLinks = document.querySelectorAll('.navbar-nav .custom-link');
    if (navLinks.length > 0) {
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Cantidad de píxeles que se moverá el carrusel en cada clic
    const scrollAmount = 320; 

    // --- LÓGICA PARA CARRUSEL DESTACADOS ---
    const carruselDestacados = document.getElementById("carrusel-destacados");
    const btnLeftDestacados = document.getElementById("btn-left-destacados");
    const btnRightDestacados = document.getElementById("btn-right-destacados");

    // Verificamos que los 3 elementos existan antes de escuchar los clics
    if (carruselDestacados && btnLeftDestacados && btnRightDestacados) {
        btnLeftDestacados.addEventListener("click", () => {
            carruselDestacados.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        btnRightDestacados.addEventListener("click", () => {
            carruselDestacados.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    }

    // --- LÓGICA PARA CARRUSEL RECOMENDADOS ---
    const carruselRecomendados = document.getElementById("carrusel-recomendados");
    const btnLeftRecomendados = document.getElementById("btn-left-recomendados");
    const btnRightRecomendados = document.getElementById("btn-right-recomendados");

    // Verificamos que los 3 elementos existan antes de escuchar los clics
    if (carruselRecomendados && btnLeftRecomendados && btnRightRecomendados) {
        btnLeftRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        btnRightRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    }
});