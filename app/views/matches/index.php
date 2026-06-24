<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="container">
    <h1 class="page-title"><i class="fas fa-heart page-title__icon"></i> Guardados</h1>
    
    <?php if($_SESSION['user_role'] == 'user'): ?>
        <p class="page-subtitle">Empleos a los que les diste like.</p>
        
        <div id="jobs-section">
            <h2 class="section-title">Empleos guardados</h2>
            <div id="matches-list" class="matches-grid">
                <p class="loading">Cargando empleos...</p>
            </div>
            <div id="no-matches" class="empty-panel">
                <i class="far fa-sad-tear fa-3x muted-icon"></i>
                <p>Aún no tienes empleos guardados.</p>
                <a href="<?php echo URLROOT; ?>/empleos" class="btn-main">Buscar Empleos</a>
            </div>
            <button id="btn-clear-jobs" class="btn-main btn-danger-soft mt-20">
                Limpiar mis matches
            </button>
        </div>
    <?php endif; ?>

    <?php if($_SESSION['user_role'] == 'recruiter'): ?>
        <p class="page-subtitle">Candidatos que seleccionaste.</p>

        <div id="candidates-section">
            <h2 class="section-title">Candidatos seleccionados</h2>
            <div id="candidates-list" class="matches-grid">
                <p class="loading">Cargando candidatos...</p>
            </div>
            <div id="no-candidates" class="empty-panel">
                <i class="fas fa-user-slash fa-3x muted-icon"></i>
                <p>Aún no has seleccionado candidatos.</p>
                <a href="<?php echo URLROOT; ?>/candidatos" class="btn-main btn-soft">Buscar Talento</a>
            </div>
            <button id="btn-clear-candidates" class="btn-main btn-danger-soft mt-20">
                Limpiar candidatos seleccionados
            </button>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const userRole = "<?php echo $_SESSION['user_role']; ?>";
        const api = window.Api || null;

        // --- EMPLEOS (Solo si es user) ---
        if(userRole === 'user'){
            const matchesList = document.getElementById('matches-list');
            const noMatches = document.getElementById('no-matches');
            const btnClearJobs = document.getElementById('btn-clear-jobs');
            const renderJobs = (jobs) => {
                if (!Array.isArray(jobs) || jobs.length === 0) {
                    matchesList.style.display = 'none';
                    noMatches.style.display = 'block';
                    return;
                }
                matchesList.style.display = 'grid';
                noMatches.style.display = 'none';
                matchesList.innerHTML = '';
                jobs.forEach(job => {
                    const card = document.createElement('div');
                    card.className = 'match-card';
                    card.innerHTML = `
                        <div class="match-icon">
                            <i class="fas ${job.icon || 'fa-briefcase'}"></i>
                        </div>
                        <div class="match-info">
                            <h3>${job.title}</h3>
                            <p>${job.company}</p>
                            <small>${job.location}</small>
                        </div>
                        <div class="match-actions">
                            <button class="btn-chat" type="button" aria-label="Abrir chat" onclick="alert('¡Próximamente Chat!')"><i class="fas fa-comment"></i></button>
                            <button class="btn-chat btn-remove-job" type="button" data-job-id="${job.id}" aria-label="Quitar de recientes"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                    matchesList.appendChild(card);
                });

                matchesList.querySelectorAll('.btn-remove-job').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const jobId = parseInt(btn.getAttribute('data-job-id') || '0', 10);
                        if (!jobId) return;

                        const likes = JSON.parse(localStorage.getItem('pegaTinder_likes')) || [];
                        localStorage.setItem('pegaTinder_likes', JSON.stringify(likes.filter(j => parseInt(j.id, 10) !== jobId)));
                        if (api && api.postForm) {
                            await api.postForm('/matches/removeJob', { job_id: jobId });
                        }
                        localStorage.setItem('user_matches_refresh', Date.now().toString());

                        const refreshed = api && api.getJson ? await api.getJson('/matches/listJobs') : null;
                        if (refreshed && refreshed.ok && Array.isArray(refreshed.data)) {
                            renderJobs(refreshed.data);
                            return;
                        }
                        renderJobs(JSON.parse(localStorage.getItem('pegaTinder_likes')) || []);
                    });
                });
            };

            (async () => {
                const resp = api && api.getJson ? await api.getJson('/matches/listJobs') : null;
                if (resp && resp.ok && Array.isArray(resp.data)) {
                    renderJobs(resp.data);
                } else {
                    const fallback = JSON.parse(localStorage.getItem('pegaTinder_likes')) || [];
                    renderJobs(fallback);
                }
            })();

            if (btnClearJobs) {
                btnClearJobs.addEventListener('click', () => {
                    localStorage.removeItem('pegaTinder_likes');
                    if(api && api.postForm){
                        api.postForm('/matches/clearJobs', {});
                    }
                    localStorage.setItem('user_matches_refresh', Date.now().toString());
                    matchesList.innerHTML = '';
                    matchesList.style.display = 'none';
                    noMatches.style.display = 'block';
                });
            }
        }

        // --- CANDIDATOS (Solo si es recruiter) ---
        if(userRole === 'recruiter'){
            const candidatesList = document.getElementById('candidates-list');
            const noCandidates = document.getElementById('no-candidates');
            const btnClearCandidates = document.getElementById('btn-clear-candidates');
            const renderCandidates = (cands) => {
                if (!Array.isArray(cands) || cands.length === 0) {
                    candidatesList.style.display = 'none';
                    noCandidates.style.display = 'block';
                    return;
                }
                candidatesList.style.display = 'grid';
                noCandidates.style.display = 'none';
                candidatesList.innerHTML = '';
                cands.forEach(cand => {
                    const card = document.createElement('div');
                    card.className = 'match-card';
                    
                    let imgHtml = '';
                    if(cand.image) {
                        imgHtml = `<div class="match-icon match-icon--img"><img src="${cand.image}" alt="" loading="lazy" decoding="async"></div>`;
                    } else {
                        imgHtml = `<div class="match-icon"><i class="fas fa-user"></i></div>`;
                    }

                    card.innerHTML = `
                        ${imgHtml}
                        <div class="match-info">
                            <h3>${cand.title || cand.name || ''}</h3>
                            <p>${cand.company || cand.profession || ''}</p>
                            <small>${cand.location || ''}</small>
                        </div>
                        <div class="match-actions">
                            <button class="btn-chat" type="button" aria-label="Contactar candidato" onclick="alert('Contactar a ${cand.title || cand.name || ''}')"><i class="fas fa-envelope"></i></button>
                        </div>
                    `;
                    candidatesList.appendChild(card);
                });
            };

            (async () => {
                const resp = api && api.getJson ? await api.getJson('/matches/listCandidates') : null;
                if (resp && resp.ok && Array.isArray(resp.data)) {
                    renderCandidates(resp.data);
                } else {
                    const fallback = JSON.parse(localStorage.getItem('pegaTinder_candidates')) || [];
                    renderCandidates(fallback);
                }
            })();

            if (btnClearCandidates) {
                btnClearCandidates.addEventListener('click', () => {
                    localStorage.removeItem('pegaTinder_candidates');
                    if(api && api.postForm){
                        api.postForm('/matches/clearCandidates', {});
                    }
                    candidatesList.innerHTML = '';
                    candidatesList.style.display = 'none';
                    noCandidates.style.display = 'block';
                });
            }
        }
    });
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
