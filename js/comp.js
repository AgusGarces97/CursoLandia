// Esperamos a que todo el HTML cargue antes de ejecutar el script
    document.addEventListener("DOMContentLoaded", () => {
        
        // Seleccionamos todos los enlaces que tienen la clase 'custom-link' dentro de la lista de navegación
        const navLinks = document.querySelectorAll('.navbar-nav .custom-link');

        // Recorremos cada enlace para agregarle un "escuchador de clics"
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                
                // 1. Cuando se hace clic, primero le quitamos la clase 'active' a TODOS los enlaces
                navLinks.forEach(l => l.classList.remove('active'));
                
                // 2. Luego, le agregamos la clase 'active' SOLAMENTE al enlace que recibió el clic (this)
                this.classList.add('active');
                
            });
        });

        // Cantidad de pixeles que se moverá el carrusel en cada clic
        const scrollAmount = 300; 

        // --- LÓGICA PARA CARRUSEL DESTACADOS ---
        const carruselDestacados = document.getElementById("carrusel-destacados");
        const btnLeftDestacados = document.getElementById("btn-left-destacados");
        const btnRightDestacados = document.getElementById("btn-right-destacados");

        btnLeftDestacados.addEventListener("click", () => {
            carruselDestacados.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        btnRightDestacados.addEventListener("click", () => {
            carruselDestacados.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });

        // --- LÓGICA PARA CARRUSEL RECOMENDADOS ---
        const carruselRecomendados = document.getElementById("carrusel-recomendados");
        const btnLeftRecomendados = document.getElementById("btn-left-recomendados");
        const btnRightRecomendados = document.getElementById("btn-right-recomendados");

        btnLeftRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        btnRightRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    });



document.addEventListener("click",  () => {

});