<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="page-center">
    <div class="panel jobs-panel">
        <h2><?php echo esc($data['title'] ?? 'Mis ofertas publicadas'); ?></h2>
        <p><?php echo esc($data['description'] ?? ''); ?></p>

        <div class="jobs-toolbar">
            <a class="btn btn-primary" href="<?php echo URLROOT; ?>/crear-oferta"><i class="fas fa-plus-circle"></i> Nueva oferta</a>
        </div>

        <div id="jobs-empty" class="jobs-empty" style="display:none;">Aún no has creado ofertas. Usa “Nueva oferta” para publicar tu primera vacante.</div>
        <div id="jobs-list" class="jobs-grid"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const api = window.Api || null;
    const listEl = document.getElementById('jobs-list');
    const emptyEl = document.getElementById('jobs-empty');

    const escapeHtml = (v) => {
        const s = (v ?? '').toString();
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/'/g, '&#039;');
    };

    const statusLabel = (s) => s === 'published' ? 'Activa' : 'Inactiva';

    const render = (items) => {
        const arr = Array.isArray(items) ? items : [];
        if (emptyEl) emptyEl.style.display = arr.length === 0 ? 'block' : 'none';
        if (!listEl) return;
        if (arr.length === 0) {
            listEl.innerHTML = '';
            return;
        }

        listEl.innerHTML = arr.map((j) => {
            const id = (j.id ?? '').toString();
            const title = escapeHtml(j.title || '');
            const loc = escapeHtml(j.location || '');
            const created = escapeHtml(j.created_at || '');
            const status = (j.status || 'draft').toString();
            const postulations = (j.postulations_count ?? 0).toString();
            const type = escapeHtml(j.type || '');
            const salary = escapeHtml(j.salary || '');

            const statusClass = status === 'published' ? 'jobs-status jobs-status--active' : 'jobs-status jobs-status--inactive';
            const toggleTo = status === 'published' ? 'draft' : 'published';
            const toggleLabel = status === 'published' ? 'Desactivar' : 'Activar';

            return `
                <div class="jobs-card" data-job-id="${escapeHtml(id)}">
                    <div class="jobs-card-top">
                        <div>
                            <div class="jobs-title">${title}</div>
                            <div class="jobs-sub">${loc} · ${type}${salary ? ` · ${salary}` : ''}</div>
                        </div>
                        <div class="${statusClass}">${escapeHtml(statusLabel(status))}</div>
                    </div>

                    <div class="jobs-meta">
                        <span class="badge"><i class="fas fa-calendar-alt"></i> ${created ? created : '—'}</span>
                        <span class="badge"><i class="fas fa-inbox"></i> ${postulations} postulaciones</span>
                    </div>

                    <div class="jobs-actions">
                        <a class="btn btn-secondary" href="${escapeHtml(window.URLROOT)}/crear-oferta?id=${escapeHtml(id)}"><i class="fas fa-pen"></i> Editar</a>
                        <button class="btn btn-soft js-toggle" type="button" data-status="${escapeHtml(toggleTo)}"><i class="fas fa-power-off"></i> ${escapeHtml(toggleLabel)}</button>
                        <button class="btn btn-danger-soft js-delete" type="button"><i class="fas fa-trash"></i> Eliminar</button>
                    </div>
                </div>
            `;
        }).join('');

        bindActions();
    };

    const load = async () => {
        if (!api || !api.getJson) return;
        const json = await api.getJson('/Recruiter/myJobsData');
        if (!json || !json.ok) {
            render([]);
            return;
        }
        render(json.data || []);
    };

    const bindActions = () => {
        if (!listEl || !api || !api.postForm) return;
        listEl.querySelectorAll('.jobs-card').forEach((card) => {
            const jobId = card.getAttribute('data-job-id');
            const btnToggle = card.querySelector('.js-toggle');
            const btnDelete = card.querySelector('.js-delete');

            if (btnToggle) {
                btnToggle.addEventListener('click', async () => {
                    const status = btnToggle.getAttribute('data-status') || 'draft';
                    const res = await api.postForm('/Recruiter/jobStatus', { job_id: jobId, status });
                    if (!res || !res.ok) return;
                    localStorage.setItem('recruiter_jobs_refresh', Date.now().toString());
                    load();
                });
            }

            if (btnDelete) {
                btnDelete.addEventListener('click', async () => {
                    const res = await api.postForm('/Recruiter/deleteJob', { job_id: jobId });
                    if (!res || !res.ok) return;
                    localStorage.setItem('recruiter_jobs_refresh', Date.now().toString());
                    load();
                });
            }
        });
    };

    window.addEventListener('storage', (e) => {
        if (e && e.key === 'recruiter_jobs_refresh') {
            load();
        }
    });

    setInterval(load, 12000);
    load();
});
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
