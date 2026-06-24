document.addEventListener('DOMContentLoaded', () => {
    // Verificar si estamos en la página de Reclutador
    const cardContent = document.getElementById('recruiter-card-content');
    const cardStack = document.getElementById('recruiter-card-stack');
    const btnNope = document.getElementById('btn-nope-recruiter');
    const btnLike = document.getElementById('btn-like-recruiter');
    const btnInfo = document.getElementById('btn-info-recruiter');
    
    // Elementos del Modal
    const modal = document.getElementById('recruiter-modal');
    const closeModal = document.querySelector('.close-modal-recruiter');
    const pipelineRoot = document.getElementById('recruiter-pipeline');

    if (cardContent) {
        let currentIndex = 0;
        const api = window.Api || null;
        let isFetching = false;

    // Función para renderizar la tarjeta actual
    function renderCard() {
        maybeFetchMore();
        if (typeof jobsData !== 'undefined' && currentIndex < jobsData.length) {
            const candidate = jobsData[currentIndex];
            
            // Animación de entrada
            cardContent.style.opacity = '0';
            cardContent.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                // Imagen del candidato
                let imageHtml = '';
                if(candidate.image) {
                    imageHtml = `<div class="card-image"><img src="${candidate.image}" alt="" loading="lazy" decoding="async"></div>`;
                } else {
                    imageHtml = `<div class="card-image" style="background-color: rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center;"><i class="fas fa-user-tie fa-5x" style="color: rgba(245, 247, 250, 0.5)"></i></div>`;
                }

                // Renderizar contenido
                cardContent.innerHTML = `
                    ${imageHtml}
                    <h3>${candidate.title}</h3>
                    <p class="company company--accent">${candidate.company}</p>
                    <div class="details">
                        <span class="badge"><i class="fas fa-map-marker-alt"></i> ${candidate.location}</span>
                        <span class="badge"><i class="fas fa-code"></i> ${candidate.type}</span>
                    </div>
                    <p class="salary">${candidate.salary}</p>
                `;
                
                // Reset estilos
                cardStack.className = 'card-demo';
                cardContent.style.opacity = '1';
                cardContent.style.transform = 'scale(1)';
                
                // Habilitar botón info
                if(btnInfo) btnInfo.style.display = 'block';
                
            }, 200);
            
        } else {
            // No hay más candidatos
            cardContent.innerHTML = `
                <i class="fas fa-check-circle fa-3x" style="color: #2196f3"></i>
                <h3>¡Estás al día!</h3>
                <p>No hay más candidatos por ahora.</p>
            `;
            // Deshabilitar botones
            if(btnNope) {
                btnNope.disabled = true;
                btnNope.style.opacity = '0.5';
            }
            if(btnLike) {
                btnLike.disabled = true;
                btnLike.style.opacity = '0.5';
            }
            if(btnInfo) btnInfo.style.display = 'none';
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
        const resp = await api.getJson(`/candidatos/feed?limit=12&offset=${offset}`);
        if(resp && resp.ok && Array.isArray(resp.data) && resp.data.length > 0){
            jobsData.push(...resp.data);
        }
        isFetching = false;
    }

    // Manejar acción de Like (Contratar/Interés)
    function handleLike() {
        if (currentIndex >= jobsData.length) return;
        
        // Guardar en LocalStorage específico de candidatos
        const candidate = jobsData[currentIndex];
        saveCandidateLike(candidate);

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

    // Guardar Like en LocalStorage (Key diferente)
    function saveCandidateLike(candidate) {
        let likes = JSON.parse(localStorage.getItem('pegaTinder_candidates')) || [];
        // Evitar duplicados
        if (!likes.some(c => c.id === candidate.id)) {
            likes.push(candidate);
            localStorage.setItem('pegaTinder_candidates', JSON.stringify(likes));
        }
        if(api && api.postForm){
            api.postForm('/matches/likeCandidate', { candidate_id: candidate.id });
        }
    }

    // Abrir Modal
    function openModal() {
        if (currentIndex >= jobsData.length) return;
        const candidate = jobsData[currentIndex];
        
        document.getElementById('modal-title').innerText = candidate.title;
        document.getElementById('modal-subtitle').innerText = candidate.company; // Profesión
        document.getElementById('modal-desc').innerText = candidate.description;
        document.getElementById('modal-salary').innerText = candidate.salary;
        document.getElementById('modal-location').innerText = candidate.location;
        
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
    }

    if (pipelineRoot) {
        const api = window.Api || null;
        const listEl = document.getElementById('pipeline-list');
        const emptyEl = document.getElementById('pipeline-empty');
        const jobEl = document.getElementById('pipeline-job');
        const statusEl = document.getElementById('pipeline-status');
        const qEl = document.getElementById('pipeline-q');
        const minExpEl = document.getElementById('pipeline-min-exp');
        const applyEl = document.getElementById('pipeline-apply');
        const defaultEmptyText = emptyEl ? emptyEl.textContent : '';

        const jobs = Array.isArray(window.RECRUITER_JOBS) ? window.RECRUITER_JOBS : [];

        const statusLabel = (s) => {
            if (s === 'postulacion_recibida') return 'Postulación recibida';
            if (s === 'entrevista') return 'En entrevista';
            if (s === 'seleccionado') return 'Seleccionado';
            if (s === 'rechazado') return 'Rechazado';
            return 'Postulación recibida';
        };

        const escapeHtml = (v) => {
            const s = (v ?? '').toString();
            return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/'/g, '&#039;');
        };

        const buildJobOptions = (selectedId) => {
            const sid = selectedId ? selectedId.toString() : '';
            const opts = ['<option value=\"\">Sin asignar</option>'];
            for (const j of jobs) {
                const id = (j && j.id != null) ? j.id.toString() : '';
                const label = `${(j.title || '').toString()} — ${(j.company || '').toString()}`;
                opts.push(`<option value=\"${escapeHtml(id)}\" ${id === sid ? 'selected' : ''}>${escapeHtml(label)}</option>`);
            }
            return opts.join('');
        };

        const render = (items) => {
            if (!listEl) return;
            const arr = Array.isArray(items) ? items : [];
            if (emptyEl) emptyEl.style.display = arr.length === 0 ? 'block' : 'none';
            if (arr.length === 0) {
                listEl.innerHTML = '';
                return;
            }

            listEl.innerHTML = arr.map((it) => {
                const candidateId = (it.candidate_id ?? '').toString();
                const entryType = (it.entry_type || 'candidate').toString();
                const name = escapeHtml(it.name || '');
                const prof = escapeHtml(it.profession || '');
                const loc = escapeHtml(it.location || '');
                const skills = escapeHtml(it.skills || '');
                const exp = (it.years_experience ?? 0);
                const status = (it.status || 'postulacion_recibida').toString();
                const jobTitle = escapeHtml(it.job_title || 'Sin asignar');
                const jobCompany = escapeHtml(it.job_company || '');
                const email = (it.email || '').toString();
                const phone = (it.phone || '').toString();
                const cv = (it.cv_url || '').toString();
                const img = (it.image || '').toString();
                const fixedJobId = (it.job_id ?? '').toString();
                const isApplicant = entryType === 'job_applicant';

                const avatar = img
                    ? `<div class=\"pipeline-avatar\"><img src=\"${escapeHtml(img)}\" alt=\"\" loading=\"lazy\" decoding=\"async\"></div>`
                    : `<div class=\"pipeline-avatar\"><i class=\"fas fa-user-tie\"></i></div>`;

                const emailHtml = email ? `<a href=\"mailto:${escapeHtml(email)}\">${escapeHtml(email)}</a>` : '<span>No disponible</span>';
                const phoneHtml = phone ? `<a href=\"tel:${escapeHtml(phone)}\">${escapeHtml(phone)}</a>` : '<span>No disponible</span>';
                const cvHtml = cv ? `<a href=\"${escapeHtml(cv)}\" target=\"_blank\" rel=\"noopener\">Ver CV</a>` : '<span>No disponible</span>';

                return `
                    <div class=\"pipeline-card\" data-candidate-id=\"${escapeHtml(candidateId)}\" data-entry-type=\"${escapeHtml(entryType)}\" data-job-id=\"${escapeHtml(fixedJobId)}\">
                        <div class=\"pipeline-card-top\">
                            ${avatar}
                            <div>
                                <div class=\"pipeline-title\">${name}</div>
                                <div class=\"pipeline-subtitle\">${prof}</div>
                                <div class=\"pipeline-meta\">
                                    <span class=\"badge\"><i class=\"fas fa-map-marker-alt\"></i> ${loc}</span>
                                    <span class=\"badge\"><i class=\"fas fa-code\"></i> ${skills || '—'}</span>
                                    <span class=\"badge\"><i class=\"fas fa-clock\"></i> ${escapeHtml(exp)} años</span>
                                </div>
                            </div>
                        </div>

                        <div class=\"pipeline-controls\">
                            <div class=\"form-group\">
                                <label class=\"label-strong\">Vacante</label>
                                <select class=\"form-control pipeline-job\" ${isApplicant ? 'disabled' : ''}>
                                    ${buildJobOptions(it.job_id)}
                                </select>
                                <div class=\"help-text\">Actual: ${jobCompany ? `${jobTitle} — ${jobCompany}` : jobTitle}</div>
                            </div>
                            <div class=\"form-group\">
                                <label class=\"label-strong\">Estado</label>
                                <select class=\"form-control pipeline-status\">
                                    <option value=\"postulacion_recibida\" ${status === 'postulacion_recibida' ? 'selected' : ''}>Postulación recibida</option>
                                    <option value=\"entrevista\" ${status === 'entrevista' ? 'selected' : ''}>En proceso de entrevista</option>
                                    <option value=\"seleccionado\" ${status === 'seleccionado' ? 'selected' : ''}>Seleccionado</option>
                                    <option value=\"rechazado\" ${status === 'rechazado' ? 'selected' : ''}>Rechazado</option>
                                </select>
                                <div class=\"help-text\">Actual: ${escapeHtml(statusLabel(status))}</div>
                            </div>
                        </div>

                        <div class=\"pipeline-contact\">
                            <div><span class=\"label-strong\">Email:</span> ${emailHtml}</div>
                            <div><span class=\"label-strong\">Teléfono:</span> ${phoneHtml}</div>
                            <div><span class=\"label-strong\">CV:</span> ${cvHtml}</div>
                        </div>

                        <div class=\"pipeline-saved\">Guardado</div>
                    </div>
                `;
            }).join('');

            attachCardHandlers();
        };

        const getFilters = () => {
            const params = new URLSearchParams();
            const jobId = jobEl ? jobEl.value : '';
            const status = statusEl ? statusEl.value : '';
            const q = qEl ? qEl.value : '';
            const minExp = minExpEl ? minExpEl.value : '0';

            if (jobId) params.set('job_id', jobId);
            if (status) params.set('status', status);
            if (q) params.set('q', q);
            if (minExp && minExp !== '0') params.set('min_exp', minExp);
            return params.toString();
        };

        const load = async () => {
            if (!api || !api.getJson) return;
            const qs = getFilters();
            const path = qs ? `/Recruiter/pipelineData?${qs}` : '/Recruiter/pipelineData';
            const json = await api.getJson(path);
            if (!json || !json.ok) {
                render([]);
                return;
            }
            if (emptyEl) {
                const filtersActive = !!(json.meta && json.meta.filters_active);
                emptyEl.textContent = filtersActive
                    ? 'No hay resultados con los filtros activos. Limpia los filtros para ver todos los postulantes.'
                    : defaultEmptyText;
            }
            render(json.data || []);
        };

        const save = async (cardEl) => {
            if (!api || !api.postForm || !cardEl) return;
            const candidateId = cardEl.getAttribute('data-candidate-id');
            const entryType = cardEl.getAttribute('data-entry-type') || 'candidate';
            const statusSel = cardEl.querySelector('.pipeline-status');
            const jobSel = cardEl.querySelector('.pipeline-job');
            const status = statusSel ? statusSel.value : 'postulacion_recibida';
            const jobId = entryType === 'job_applicant'
                ? (cardEl.getAttribute('data-job-id') || '')
                : (jobSel ? jobSel.value : '');

            const res = await api.postForm('/Recruiter/pipelineUpdate', {
                candidate_id: candidateId,
                status: status,
                job_id: jobId,
                entry_type: entryType
            });
            if (!res || !res.ok) return;

            const saved = cardEl.querySelector('.pipeline-saved');
            if (saved) {
                saved.style.display = 'block';
                setTimeout(() => {
                    saved.style.display = 'none';
                }, 1200);
            }
        };

        const attachCardHandlers = () => {
            if (!listEl) return;
            const cards = listEl.querySelectorAll('.pipeline-card');
            cards.forEach((card) => {
                const statusSel = card.querySelector('.pipeline-status');
                const jobSel = card.querySelector('.pipeline-job');
                if (statusSel) statusSel.addEventListener('change', () => save(card));
                if (jobSel) jobSel.addEventListener('change', () => save(card));
            });
        };

        if (applyEl) applyEl.addEventListener('click', load);
        load();
    }
});
