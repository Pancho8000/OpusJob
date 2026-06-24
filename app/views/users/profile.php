<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="page-center">
    <div class="panel">
        <h2>
            <i class="fas fa-user-cog"></i>
            <?php echo (isset($data['mode']) && $data['mode'] === 'public') ? 'Perfil' : 'Mi Perfil'; ?>
        </h2>
        <p>
            <?php
                if(isset($data['mode']) && $data['mode'] === 'public'){
                    echo 'Información pública del reclutador y ofertas activas.';
                } else {
                    echo (isset($data['mode']) && $data['mode'] === 'view') ? 'Visualiza tu información' : 'Actualiza tus datos y preferencias';
                }
            ?>
        </p>
        <?php flash('profile_success'); ?>
        <?php if(isset($data['mode']) && ($data['mode'] === 'view' || $data['mode'] === 'public')): ?>
            <div class="profile-header">
                <?php if(!empty($data['avatar'])): ?>
                    <img class="avatar avatar--lg" src="<?php echo URLROOT; ?>/img/<?php echo $data['avatar']; ?>" alt="Foto de perfil" loading="lazy" decoding="async">
                <?php else: ?>
                    <div class="avatar-placeholder avatar-placeholder--lg">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div class="profile-meta">
                    <div class="profile-name"><?php echo esc($data['name']); ?></div>
                    <div class="profile-location"><?php echo esc($data['location']); ?></div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">Tipo de cuenta</div>
                <div class="info-value"><?php echo $data['role'] === 'recruiter' ? 'Reclutador' : 'Candidato'; ?></div>
            </div>

            <?php if(isset($data['mode']) && $data['mode'] === 'view'): ?>
                <div class="info-card mt-16">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo esc($data['email']); ?></div>
                </div>
                <a href="<?php echo URLROOT; ?>/editar-perfil" class="btn-main btn-block mt-20">Editar perfil</a>
            <?php endif; ?>

            <?php if(($data['mode'] ?? '') === 'public' || (($data['mode'] ?? '') === 'view' && ($data['role'] ?? '') === 'recruiter')): ?>
                <div class="section-title mt-20">
                    <?php echo ($data['mode'] ?? '') === 'public' ? 'Ofertas activas' : 'Mis ofertas'; ?>
                </div>
                <div id="recruiter-offers-empty" class="jobs-empty">
                    <?php echo ($data['mode'] ?? '') === 'public' ? 'Sin ofertas activas.' : 'Sin ofertas aún.'; ?>
                </div>
                <div id="recruiter-offers" class="jobs-grid"></div>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const api = window.Api || null;
                    const listEl = document.getElementById('recruiter-offers');
                    const emptyEl = document.getElementById('recruiter-offers-empty');
                    const mode = <?php echo json_encode($data['mode'] ?? 'view'); ?>;
                    const recruiterId = <?php echo (int)($data['id'] ?? 0); ?>;

                    const escapeHtml = (v) => {
                        const s = (v ?? '').toString();
                        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/'/g, '&#039;');
                    };

                    const render = (items) => {
                        const arr = Array.isArray(items) ? items : [];
                        if (emptyEl) emptyEl.style.display = arr.length === 0 ? 'block' : 'none';
                        if (!listEl) return;
                        listEl.innerHTML = arr.map((j) => {
                            const title = escapeHtml(j.title || '');
                            const loc = escapeHtml(j.location || '');
                            const created = escapeHtml(j.created_at || '');
                            const postulations = (j.postulations_count ?? 0).toString();
                            const status = (j.status || 'draft').toString();
                            const isPublished = status === 'published';
                            const statusText = mode === 'public' ? 'Activa' : (isPublished ? 'Activa' : 'Borrador');
                            const statusClass = isPublished ? 'jobs-status--active' : 'jobs-status--inactive';
                            return `
                                <div class="jobs-card jobs-card--compact">
                                    <div class="jobs-card-top">
                                        <div>
                                            <div class="jobs-title">${title}</div>
                                            <div class="jobs-sub">${loc}</div>
                                        </div>
                                        <div class="jobs-status ${statusClass}">${statusText}</div>
                                    </div>
                                    <div class="jobs-meta">
                                        <span class="badge"><i class="fas fa-calendar-alt"></i> ${created ? created : '—'}</span>
                                        <span class="badge"><i class="fas fa-inbox"></i> ${postulations} postulaciones</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    };

                    const load = async () => {
                        if (!api || !api.getJson) {
                            if (emptyEl) emptyEl.style.display = 'block';
                            return;
                        }
                        const isPublic = mode === 'public';
                        const path = isPublic
                            ? `/Users/recruiterOffersData/${recruiterId}`
                            : '/Users/myAccountJobsData';
                        const json = await api.getJson(path);
                        if (!json || !json.ok) {
                            render([]);
                            return;
                        }
                        const all = (json.data || []);
                        const visible = isPublic ? all.filter(j => (j.status || 'draft') === 'published') : all;
                        render(visible);
                    };

                    window.addEventListener('storage', (e) => {
                        if (e && e.key === 'recruiter_jobs_refresh') load();
                    });
                    setInterval(load, 12000);
                    load();
                });
                </script>
            <?php endif; ?>
        <?php else: ?>
        <form action="<?php echo URLROOT; ?>/editar-perfil" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" value="<?php echo $data['email']; ?>" disabled>
            </div>

            <div class="form-group">
                <label>Foto de perfil</label>
                <div class="upload-row">
                    <?php if(!empty($data['avatar'])): ?>
                        <img class="avatar avatar--md" src="<?php echo URLROOT; ?>/img/<?php echo $data['avatar']; ?>" alt="Foto de perfil" loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="avatar-placeholder avatar-placeholder--md">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <input type="file" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <div class="help-text">JPG/PNG/WEBP, máximo 2MB</div>
                        <span class="invalid-feedback"><?php echo $data['avatar_err']; ?></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Nombre: <sup>*</sup></label>
                <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo esc($data['name']); ?>">
                <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
            </div>

            <div class="form-group">
                <label for="location">Ubicación (Ciudad): <sup>*</sup></label>
                <input type="text" name="location" class="form-control <?php echo (!empty($data['location_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo esc($data['location']); ?>" placeholder="Ej: Santiago, Viña del Mar, Remoto">
                <span class="invalid-feedback"><?php echo $data['location_err']; ?></span>
            </div>

            <div class="form-group mt-20">
                <label class="label-strong">Cambiar contraseña (opcional)</label>
            </div>

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar nueva contraseña</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="">
                <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
            </div>

            <div class="row">
                <div class="col">
                    <input type="submit" value="Guardar cambios" class="btn-main btn-block">
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
