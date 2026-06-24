document.addEventListener('DOMContentLoaded', () => {
    // Verificar si estamos en la página de inicio (buscando elementos)
    const cardContent = document.getElementById('card-content');
    const cardStack = document.getElementById('card-stack');
    const jobsCounter = document.getElementById('jobs-counter');
    const btnNope = document.getElementById('btn-nope');
    const btnLike = document.getElementById('btn-like');
    const btnInfo = document.getElementById('btn-info');
    
    // Elementos del Modal
    const modal = document.getElementById('job-modal');
    const closeModal = document.querySelector('.close-modal');
    
    // Si no existen elementos principales, salir (estamos en otra página)
    if (!cardContent) return;
    
    let currentIndex = 0;
    const api = window.Api || null;
    let isFetching = false;

    // Función para actualizar contador
    function updateCounter() {
        if (!jobsCounter || typeof jobsData === 'undefined') return;
        const restantes = Math.max(jobsData.length - currentIndex, 0);
        if (restantes > 0) {
            jobsCounter.textContent = `${restantes} oportunidad${restantes === 1 ? '' : 'es'} disponible${restantes === 1 ? '' : 's'}`;
        } else {
            jobsCounter.textContent = 'Sin más oportunidades por ahora';
        }
    }

    // Función para renderizar la tarjeta actual
    function renderCard() {
        maybeFetchMore();
        if (typeof jobsData !== 'undefined' && currentIndex < jobsData.length) {
            const job = jobsData[currentIndex];
            
            // Animación de entrada
            cardContent.style.opacity = '0';
            cardContent.style.transform = 'scale(0.95)';
            
            // Reset botones y estilos si volvemos de empty state
            cardStack.style.boxShadow = '';
            cardStack.style.background = '';
            if(btnNope) btnNope.style.display = 'flex';
            if(btnLike) btnLike.style.display = 'flex';
            if(btnInfo) btnInfo.style.display = 'block';

            setTimeout(() => {
                // Si hay imagen, la usamos de fondo o destacada
                let imageHtml = '';
                if(job.image && job.image !== 'default.jpg') {
                    imageHtml = `<div class="card-image"><img src="${job.image}" alt="" loading="lazy" decoding="async"></div>`;
                } else {
                    imageHtml = `<i class="fas ${job.icon || 'fa-briefcase'} fa-3x"></i>`;
                }

                cardContent.innerHTML = `
                    ${imageHtml}
                    <h3>${job.title}</h3>
                    <p class="company">${job.company}</p>
                    <div class="details">
                        <span class="badge"><i class="fas fa-map-marker-alt"></i> ${job.location}</span>
                        <span class="badge"><i class="fas fa-clock"></i> ${job.type}</span>
                    </div>
                    <p class="salary">${job.salary}</p>
                `;
                
                // Reset estilos
                cardStack.className = 'card-demo';
                cardContent.style.opacity = '1';
                cardContent.style.transform = 'scale(1)';
                
                // Habilitar botón info
                if(btnInfo) btnInfo.style.display = 'block';

                updateCounter();
                
            }, 200);
            
        } else {
            // No hay más trabajos
            cardStack.innerHTML = `
                <div class="empty-state">
                    <div class="briefcase-loader">
                        <i class="fas fa-briefcase fa-3x" style="color: var(--primary);"></i>
                        <div class="spinner-ring"></div>
                    </div>
                    <h3>Estamos buscando oportunidades...</h3>
                </div>
            `;
            // Asegurar que el contenedor tenga estilo limpio
            cardStack.style.boxShadow = 'none';
            cardStack.style.background = 'transparent';

            // Ocultar botones de acción
            if(btnNope) btnNope.style.display = 'none';
            if(btnLike) btnLike.style.display = 'none';
            if(btnInfo) btnInfo.style.display = 'none';

            updateCounter();
        }
    }

    async function maybeFetchMore(){
        if(!api || !api.getJson) return;
        if(typeof jobsData === 'undefined') return;
        if(isFetching) return;
        const remaining = jobsData.length - currentIndex;
        if(remaining > 4) return;
        isFetching = true;
        const offset = jobsData.length;
        const resp = await api.getJson(`/empleos/feed?limit=12&offset=${offset}`);
        if(resp && resp.ok && Array.isArray(resp.data) && resp.data.length > 0){
            jobsData.push(...resp.data);
        }
        isFetching = false;
    }

    // Manejar acción de Like
    function handleLike() {
        if (currentIndex >= jobsData.length) return;
        
        // Guardar en LocalStorage
        const job = jobsData[currentIndex];
        saveLike(job);

        cardStack.classList.add('swipe-right');
        setTimeout(() => {
            currentIndex++;
            renderCard();
        }, 300);
    }

    // Manejar acción de Nope
    function handleNope() {
        if (currentIndex >= jobsData.length) return;

        cardStack.classList.add('swipe-left');
        setTimeout(() => {
            currentIndex++;
            renderCard();
        }, 300);
    }

    // Guardar Like en LocalStorage
    function saveLike(job) {
        let likes = JSON.parse(localStorage.getItem('pegaTinder_likes')) || [];
        // Evitar duplicados
        if (!likes.some(j => j.id === job.id)) {
            likes.push(job);
            localStorage.setItem('pegaTinder_likes', JSON.stringify(likes));
        }
        localStorage.setItem('user_matches_refresh', Date.now().toString());
        if(api && api.postForm){
            api.postForm('/matches/likeJob', { job_id: job.id });
        }
    }

    // Abrir Modal
    function openModal() {
        if (currentIndex >= jobsData.length) return;
        const job = jobsData[currentIndex];
        
        document.getElementById('modal-title').innerText = job.title;
        document.getElementById('modal-company').innerText = job.company;
        document.getElementById('modal-desc').innerText = job.description || ('Descripción no disponible todavía para ' + job.title);
        document.getElementById('modal-salary').innerText = job.salary;
        document.getElementById('modal-location').innerText = job.location;
        
        modal.style.display = 'block';
    }

    // Event Listeners
    if(btnLike) btnLike.addEventListener('click', handleLike);
    if(btnNope) btnNope.addEventListener('click', handleNope);
    if(btnInfo) btnInfo.addEventListener('click', openModal);
    
    if(closeModal) {
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', (e) => {
        if (e.target == modal) {
            modal.style.display = 'none';
        }
    });

    // Teclado (Flechas)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') handleLike();
        if (e.key === 'ArrowLeft') handleNope();
    });

    // Inicializar
    renderCard();
    updateCounter();
});
