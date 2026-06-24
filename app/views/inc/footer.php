    <?php if(isset($_SESSION['user_id'])) : ?>
            </main>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['user_id'])) : ?>
        <nav class="mobile-nav" aria-label="Navegación">
            <?php if($_SESSION['user_role'] == 'recruiter') : ?>
                <a href="<?php echo URLROOT; ?>/candidatos"><i class="fas fa-briefcase"></i><span>Candidatos</span></a>
                <a href="<?php echo URLROOT; ?>/crear-oferta"><i class="fas fa-plus-circle"></i><span>Crear</span></a>
                <a href="<?php echo URLROOT; ?>/mis-ofertas"><i class="fas fa-clipboard-list"></i><span>Ofertas</span></a>
                <a href="<?php echo URLROOT; ?>/reclutamiento"><i class="fas fa-tasks"></i><span>Pipeline</span></a>
            <?php else : ?>
                <a href="<?php echo URLROOT; ?>/empleos"><i class="fas fa-fire"></i><span>Empleos</span></a>
            <?php endif; ?>
            <a href="<?php echo URLROOT; ?>/guardados"><i class="fas fa-heart"></i><span>Guardados</span></a>
            <a href="<?php echo URLROOT; ?>/mi-perfil"><i class="fas fa-user"></i><span>Perfil</span></a>
            <a href="<?php echo URLROOT; ?>/users/logout"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a>
        </nav>
    <?php endif; ?>

    <?php $mainVer = @filemtime(dirname(APPROOT) . '/public/js/main.js'); ?>
    <script src="<?php echo URLROOT; ?>/js/main.js?v=<?php echo $mainVer ? $mainVer : time(); ?>"></script>
    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'recruiter'): ?>
        <?php $recruiterVer = @filemtime(dirname(APPROOT) . '/public/js/recruiter.js'); ?>
        <script src="<?php echo URLROOT; ?>/js/recruiter.js?v=<?php echo $recruiterVer ? $recruiterVer : time(); ?>"></script>
    <?php endif; ?>

    <?php if(isset($_SESSION['user_id'])) : ?>
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const recents = document.getElementById('sidebar-recents');
                const api = window.Api || null;
                if (!recents || !api || !api.getJson) return;

                const role = <?php echo json_encode($_SESSION['user_role']); ?>;
                const path = role === 'recruiter' ? '/matches/listCandidates' : '/matches/listJobs';

                const loadRecents = async () => {
                    try {
                        const json = await api.getJson(path);
                        if (!json || !json.ok || !Array.isArray(json.data) || json.data.length === 0) {
                            recents.innerHTML = '<div class="sidebar-muted">Sin matches aún</div>';
                            return;
                        }

                        const items = json.data.slice(0, 6);
                        recents.innerHTML = items.map(item => {
                            const title = (item.title || item.name || '').toString();
                            const subtitle = (item.company || item.profession || item.location || '').toString();
                            return `
                                <div class="sidebar-card">
                                    <div class="sidebar-card-title">${title}</div>
                                    <div class="sidebar-card-sub">${subtitle}</div>
                                </div>
                            `;
                        }).join('');
                    } catch (e) {
                        recents.innerHTML = '<div class="sidebar-muted">Sin conexión</div>';
                    }
                };

                window.addEventListener('storage', (e) => {
                    if (e && e.key === 'user_matches_refresh') {
                        loadRecents();
                    }
                });

                loadRecents();
            });
        </script>
    <?php endif; ?>
</body>
</html>
