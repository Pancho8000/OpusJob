<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="page-center">
    <div class="panel panel--sm">
        <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
        <p>Complete sus datos para ingresar</p>
        <?php flash('register_success'); ?>
        <form action="<?php echo URLROOT; ?>/login" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="email">Email: <sup>*</sup></label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Contraseña: <sup>*</sup></label>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>
            <div class="form-actions">
                <input type="submit" value="Ingresar" class="btn-main btn-block">
                <div class="form-actions__link">
                    <a class="link-muted" href="<?php echo URLROOT; ?>/registro">¿No tienes cuenta? Regístrate</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
