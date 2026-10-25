// Esperamos a que todo el HTML cargue antes de ejecutar el script
    document.addEventListener("DOMContentLoaded", () => {
        
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