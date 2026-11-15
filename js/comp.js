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

    if (carruselRecomendados && btnLeftRecomendados && btnRightRecomendados) {
        btnLeftRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        btnRightRecomendados.addEventListener("click", () => {
            carruselRecomendados.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    }

    // --- LÓGICA DE GESTIÓN DE ETIQUETAS (nuevoCurso.php, editarCurso.php, perfil.php) ---
    const inputEtiqueta = document.getElementById('buscadorEtiquetas');
    const contenedorEtiquetas = document.getElementById('contenedorEtiquetas');

    if (inputEtiqueta && contenedorEtiquetas) {
        function agregarEtiqueta(nombre) {
            let textoLimpio = nombre.trim().replace(/^#+/, '');
            if (textoLimpio === '') return;

            const existentes = Array.from(contenedorEtiquetas.querySelectorAll('input[name="etiquetas[]"]'))
                .map(inp => inp.value.toLowerCase());

            if (existentes.includes(textoLimpio.toLowerCase())) {
                inputEtiqueta.value = '';
                return;
            }

            const badge = document.createElement('span');
            badge.className = 'badge bg-white text-secondary border p-2 d-flex align-items-center gap-1 item-etiqueta';
            badge.innerHTML = `
                #${textoLimpio}
                <input type="hidden" name="etiquetas[]" value="${textoLimpio}">
                <button type="button" class="btn-close btn-remover-etiqueta" style="font-size: 0.5rem;" aria-label="Remover"></button>
            `;

            contenedorEtiquetas.appendChild(badge);
            inputEtiqueta.value = '';
        }

        inputEtiqueta.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                agregarEtiqueta(this.value);
            }
        });

        inputEtiqueta.addEventListener('change', function() {
            agregarEtiqueta(this.value);
        });

        contenedorEtiquetas.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remover-etiqueta')) {
                e.target.closest('.item-etiqueta').remove();
            }
        });
    }

    // --- VALIDACIÓN EN TIEMPO REAL (Registro) ---
    const formRegistro = document.getElementById('formRegistro');
    const inputDni = document.getElementById('dni');
    const inputEdad = document.getElementById('edad');
    const btnCrearCuenta = document.querySelector('button[name="botonCrearCuenta"]');
    
    if(formRegistro && inputDni && inputEdad && btnCrearCuenta){
        
        // Elementos para mostrar errores
        const errorDni = document.getElementById('error-dni');
        const errorEdad = document.getElementById('error-edad');

        function verificarDatos() {
            let dniValido = true;
            let edadValida = true;

            // Validación DNI: Solo números
            const regexDni = /^[0-9]+$/;
            if (inputDni.value.length > 0 && !regexDni.test(inputDni.value)) {
                inputDni.style.borderColor = 'red';
                errorDni.style.display = 'block';
                dniValido = false;
            } else {
                inputDni.style.borderColor = ''; // Restaura el color original
                errorDni.style.display = 'none';
            }

            // Validación Edad: Mínimo 10 años
            if (inputEdad.value !== "" && parseInt(inputEdad.value) < 10) {
                inputEdad.style.borderColor = 'red';
                errorEdad.style.display = 'block';
                edadValida = false;
            } else {
                inputEdad.style.borderColor = ''; // Restaura el color original
                errorEdad.style.display = 'none';
            }

            // Deshabilitar botón si hay errores
            btnCrearCuenta.disabled = !(dniValido && edadValida);
            return (dniValido && edadValida);
        }

        // Eventos 'input' para validar mientras el usuario escribe
        inputDni.addEventListener('input', verificarDatos);
        inputEdad.addEventListener('input', verificarDatos);

        // Prevenir envío si hay error
        formRegistro.addEventListener('submit', function(e) {
            if (!verificarDatos()) {
                e.preventDefault();
            }
        });
    }

    // --- PREVISUALIZACIÓN DE FOTO DE PERFIL (perfil.php) ---
    const inputFoto = document.getElementById('nueva_imagen_perfil');
    const formFoto = document.getElementById('formCambioFoto');
    const avatarImg = document.getElementById('avatar-img');

    if(inputFoto && formFoto && avatarImg){
        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if(!file.type.match('image.*') || file.size > 2 * 1024 * 1024){
                    alert("Por favor, selecciona una imagen válida menor a 2MB.");
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarImg.src = e.target.result;
                }
                reader.readAsDataURL(file);
                formFoto.submit();
            }
        });
    }
});