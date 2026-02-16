// =================================================================
// ARCHIVO JAVASCRIPT PRINCIPAL (VERSIÓN FINAL Y SIMPLIFICADA)
// =================================================================

// --- SECCIÓN 1: Tareas que se ejecutan cuando el HTML está listo ---
document.addEventListener('DOMContentLoaded', () => {

    // --- LÓGICA PARA EL CARRUSEL INFINITO DE CATEGORÍAS ---
    const scrollers = document.querySelectorAll(".scroller");
    if (scrollers.length > 0 && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        scrollers.forEach((scroller) => {
            scroller.setAttribute("data-animated", true);
        });
    }

    // --- LÓGICA PARA EL TEXTO ANIMADO DEL HERO ---
    const typedTextContainer = document.getElementById("typed-text-container");
    if (typedTextContainer) {
        const words = [
            { text: "favoritos", colorClass: "typed-text-color-1" },
            { text: "de casa", colorClass: "typed-text-color-2" },
            { text: "del día", colorClass: "typed-text-color-3" },
            { text: "locales", colorClass: "typed-text-color-4" }
        ];
        let wordIndex = 0, charIndex = 0, isDeleting = false;
        function type() {
            // ... (Esta función no necesita cambios)
            const currentWord = words[wordIndex];
            const fullText = currentWord.text;
            typedTextContainer.className = 'typed-text-container ' + currentWord.colorClass;
            if (isDeleting) {
                typedTextContainer.textContent = fullText.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typedTextContainer.textContent = fullText.substring(0, charIndex + 1);
                charIndex++;
            }
            let typeSpeed = isDeleting ? 100 : 200;
            if (!isDeleting && charIndex === fullText.length) {
                typeSpeed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                typeSpeed = 500;
            }
            setTimeout(type, typeSpeed);
        }
        type();
    }

    // --- LÓGICA PARA LA HAMBURGUESA VOLADORA ---
    const burger = document.getElementById('falling-burger');
    const targetSection = document.getElementById('restaurantes-section');
    if (burger && targetSection) {
        const observer = new IntersectionObserver((entries) => {
            burger.classList.toggle('visible', entries[0].isIntersecting);
        }, { threshold: 0.1 });
        observer.observe(targetSection);
    }
});

// --- SECCIÓN 2: Tareas que se ejecutan cuando TODA la página ha cargado ---
window.addEventListener('load', function () {
    
    // 1. Ocultar el preloader (CON RETRASO PARA QUE SE VEA LA HAMBURGUESA)
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Esperamos 1500 milisegundos (1.5 segundos) antes de quitarlo
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 1500); 
    }

    // 2. LÓGICA DEL HEADER (SIMPLE Y DIRECTA)
    const navbar = document.querySelector('.navbar.sticky-top'); // OJO: Cambié .fixed-top por .sticky-top que es la clase que usas en PHP
    if (navbar) {
        
        function handleHeaderVisibility() {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        }

        handleHeaderVisibility(); // Establece el estado inicial al cargar
        window.addEventListener('scroll', handleHeaderVisibility, { passive: true });
    }
});