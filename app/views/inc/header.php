<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] : SITENAME; ?></title>
    <?php $cssVer = @filemtime(dirname(APPROOT) . '/public/css/style.css'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css?v=<?php echo $cssVer ? $cssVer : time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script>
        window.URLROOT = <?php echo json_encode(URLROOT); ?>;
        window.CSRF_TOKEN = <?php echo json_encode(generate_csrf_token()); ?>;
        window.Api = (() => {
            const base = (window.URLROOT || '').replace(/\/$/, '');
            const encodeForm = (data) =>
                Object.keys(data || {})
                    .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
                    .join('&');

            const postForm = async (path, data) => {
                if (!base) return null;
                try {
                    const csrf = window.CSRF_TOKEN || '';
                    const payload = Object.assign({ csrf_token: csrf }, data || {});
                    const res = await fetch(`${base}${path}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-CSRF-Token': csrf
                        },
                        body: encodeForm(payload)
                    });
                    return res;
                } catch (e) {
                    return null;
                }
            };

            const getJson = async (path) => {
                if (!base) return null;
                try {
                    const res = await fetch(`${base}${path}`);
                    return await res.json();
                } catch (e) {
                    return null;
                }
            };

            return { base, postForm, getJson };
        })();
    </script>
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? ('logged-in role-' . $_SESSION['user_role']) : 'logged-out'; ?>">
    <div class="navbar">
        <div class="logo">
            <?php if(isset($_SESSION['user_id'])) : ?>
                <?php if($_SESSION['user_role'] == 'recruiter') : ?>
                    <a class="brand-link" href="<?php echo URLROOT; ?>/candidatos">OpusJob</a>
                <?php else : ?>
                    <a class="brand-link" href="<?php echo URLROOT; ?>/empleos">OpusJob</a>
                <?php endif; ?>
            <?php else : ?>
                <a class="brand-link" href="<?php echo URLROOT; ?>/login">OpusJob</a>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <?php if(isset($_SESSION['user_id'])) : ?>
                <?php if($_SESSION['user_role'] == 'user') : ?>
                    <a href="<?php echo URLROOT; ?>/empleos"><i class="fas fa-fire"></i> Empleos</a>
                <?php endif; ?>
                
                <?php if($_SESSION['user_role'] == 'recruiter') : ?>
                    <a href="<?php echo URLROOT; ?>/candidatos"><i class="fas fa-briefcase"></i> Candidatos</a>
                    <a href="<?php echo URLROOT; ?>/crear-oferta"><i class="fas fa-plus-circle"></i> Crear Oferta</a>
                    <a href="<?php echo URLROOT; ?>/mis-ofertas"><i class="fas fa-clipboard-list"></i> Mis ofertas publicadas</a>
                    <a href="<?php echo URLROOT; ?>/reclutamiento"><i class="fas fa-tasks"></i> Reclutamiento</a>
                <?php endif; ?>

                <a href="<?php echo URLROOT; ?>/mi-perfil"><i class="fas fa-user"></i> Mi perfil</a>
                <a href="<?php echo URLROOT; ?>/guardados"><i class="fas fa-heart"></i> Guardados</a>
                <a href="<?php echo URLROOT; ?>/users/logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            <?php else : ?>
                <a href="<?php echo URLROOT; ?>/registro">Registrarse</a>
                <a href="<?php echo URLROOT; ?>/login">Ingresar</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(isset($_SESSION['user_id'])) : ?>
        <div class="app-shell">
            <aside class="sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-title">OpusJob</div>
                    <div class="sidebar-subtitle">Oportunidades cerca de ti</div>
                </div>

                <a class="sidebar-user" href="<?php echo URLROOT; ?>/mi-perfil">
                    <div class="sidebar-user-inner">
                        <?php if(isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])): ?>
                            <img class="avatar avatar--sm" src="<?php echo URLROOT; ?>/img/<?php echo $_SESSION['user_avatar']; ?>" alt="Foto de perfil" loading="lazy" decoding="async">
                        <?php else: ?>
                            <div class="avatar-placeholder avatar-placeholder--sm">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="sidebar-user-text">
                            <div class="sidebar-user-name"><?php echo esc($_SESSION['user_name']); ?></div>
                            <div class="sidebar-user-meta"><?php echo esc($_SESSION['user_location'] ?? ''); ?></div>
                        </div>
                    </div>
                </a>

                <nav class="sidebar-nav">
                    <?php if($_SESSION['user_role'] == 'user') : ?>
                        <a class="sidebar-link" href="<?php echo URLROOT; ?>/empleos"><i class="fas fa-fire"></i><span>Empleos</span></a>
                    <?php endif; ?>
                    <?php if($_SESSION['user_role'] == 'recruiter') : ?>
                        <a class="sidebar-link" href="<?php echo URLROOT; ?>/candidatos"><i class="fas fa-briefcase"></i><span>Candidatos</span></a>
                        <a class="sidebar-link" href="<?php echo URLROOT; ?>/crear-oferta"><i class="fas fa-plus-circle"></i><span>Crear Oferta</span></a>
                        <a class="sidebar-link" href="<?php echo URLROOT; ?>/mis-ofertas"><i class="fas fa-clipboard-list"></i><span>Mis ofertas</span></a>
                        <a class="sidebar-link" href="<?php echo URLROOT; ?>/reclutamiento"><i class="fas fa-tasks"></i><span>Reclutamiento</span></a>
                    <?php endif; ?>
                    <a class="sidebar-link" href="<?php echo URLROOT; ?>/mi-perfil"><i class="fas fa-user"></i><span>Mi perfil</span></a>
                    <a class="sidebar-link" href="<?php echo URLROOT; ?>/editar-perfil"><i class="fas fa-pen"></i><span>Editar perfil</span></a>
                    <a class="sidebar-link" href="<?php echo URLROOT; ?>/guardados"><i class="fas fa-heart"></i><span>Guardados</span></a>
                </nav>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Recientes</div>
                    <div id="sidebar-recents" class="sidebar-recents">
                        <div class="sidebar-muted">Cargando...</div>
                    </div>
                </div>

                <div class="sidebar-footer">
                    <a class="sidebar-link sidebar-logout" href="<?php echo URLROOT; ?>/users/logout"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></a>
                </div>
            </aside>

            <main class="app-content">
    <?php endif; ?>
