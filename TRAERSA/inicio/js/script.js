document.addEventListener('DOMContentLoaded', () => {

    /* =====================================================
       Header — solid background after scrolling
       ===================================================== */
    const header = document.getElementById('siteHeader');
    const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 40);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });


    /* =====================================================
       Mobile navigation
       ===================================================== */
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');

    function closeNav(){
        navToggle.classList.remove('is-open');
        mainNav.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
    }

    navToggle.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('is-open');
        navToggle.classList.toggle('is-open', isOpen);
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    mainNav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeNav);
    });


    /* =====================================================
       Hero carousel
       ===================================================== */
    const track = document.getElementById('heroTrack');
    const slides = Array.from(track.querySelectorAll('.hero-slide'));
    const dotsWrap = document.getElementById('heroDots');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    const hero = document.getElementById('hero');

    let current = slides.findIndex(s => s.classList.contains('is-active'));
    if (current < 0) current = 0;

    let autoplayTimer = null;
    const AUTOPLAY_MS = 6500;

    // Build dots
    const dots = slides.map((_, i) => {
        const dot = document.createElement('button');
        dot.className = 'dot' + (i === current ? ' active' : '');
        dot.setAttribute('aria-label', `Ir a la diapositiva ${i + 1}`);
        dot.addEventListener('click', () => goTo(i, true));
        dotsWrap.appendChild(dot);
        return dot;
    });

    function render(){
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
    }

    function goTo(index, userTriggered){
        current = (index + slides.length) % slides.length;
        render();
        if (userTriggered) restartAutoplay();
    }

    function next(){ goTo(current + 1); }
    function prev(){ goTo(current - 1); }

    function startAutoplay(){
        autoplayTimer = setInterval(next, AUTOPLAY_MS);
    }
    function stopAutoplay(){
        clearInterval(autoplayTimer);
    }
    function restartAutoplay(){
        stopAutoplay();
        startAutoplay();
    }

    nextBtn.addEventListener('click', () => goTo(current + 1, true));
    prevBtn.addEventListener('click', () => goTo(current - 1, true));

    hero.addEventListener('mouseenter', stopAutoplay);
    hero.addEventListener('mouseleave', startAutoplay);
    hero.addEventListener('focusin', stopAutoplay);
    hero.addEventListener('focusout', startAutoplay);

    // Keyboard arrows while the hero is in view
    let heroInView = true;
    document.addEventListener('keydown', (e) => {
        if (!heroInView) return;
        if (e.key === 'ArrowRight') goTo(current + 1, true);
        if (e.key === 'ArrowLeft') goTo(current - 1, true);
    });

    // Touch swipe
    let touchStartX = 0;
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        const delta = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(delta) > 40){
            delta < 0 ? goTo(current + 1, true) : goTo(current - 1, true);
        }
    }, { passive: true });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches){
        // honour the user's preference — no autoplay
    } else {
        startAutoplay();
    }


    /* =====================================================
       Scroll-reveal
       ===================================================== */
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting){
                entry.target.classList.add('in-view');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));

    const heroVisibilityObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => { heroInView = entry.isIntersecting; });
    }, { threshold: 0.2 });
    heroVisibilityObserver.observe(hero);


    /* =====================================================
       Service modal
       ===================================================== */
    const modal = document.getElementById('miModal');
    const titulo = document.getElementById('tituloModal');
    const texto = document.getElementById('textoModal');
    const imagen = document.getElementById('imagenModal');
    const closeBtn = document.getElementById('closeModal');

    const SERVICES = {
        aduanal: {
            titulo: 'GESTIÓN ADUANAL',
            texto: 'Ofrecemos asesoría completa en trámites de aduana y cumplimiento legal, agilizando cada importación y exportación.',
            img: 'imagenes/aduana.jpg'
        },
        terrestre: {
            titulo: 'TRANSPORTE TERRESTRE',
            texto: 'Servicio de transporte con monitoreo constante y seguridad garantizada en cada ruta, local o regional.',
            img: 'imagenes/transporte.jpg'
        },
        almacenaje: {
            titulo: 'ALMACENAJE',
            texto: 'Contamos con amplias bodegas y control estricto de inventarios, listas para adaptarse a su operación.',
            img: 'imagenes/almacen.jpg'
        },
        proyectos: {
            titulo: 'PROYECTOS ESPECIALES',
            texto: 'Soluciones logísticas a la medida para cargas sobredimensionadas, delicadas o de alta complejidad.',
            img: 'imagenes/proyecto.jpg'
        }
    };

    let lastFocused = null;

    function openModal(key){
        const data = SERVICES[key];
        if (!data) return;
        titulo.textContent = data.titulo;
        texto.textContent = data.texto;
        imagen.src = data.img;
        imagen.alt = data.titulo;

        lastFocused = document.activeElement;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    function closeModal(){
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocused) lastFocused.focus();
    }

    document.querySelectorAll('[data-modal]').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn.dataset.modal));
    });

    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });


    /* =====================================================
       Footer year
       ===================================================== */
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

});
