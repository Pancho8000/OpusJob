<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="page-center">
    <div class="panel">
        <h2><i class="fas fa-user-plus"></i> Registrarse</h2>
        <p>Crea una cuenta para empezar</p>
        <form action="<?php echo URLROOT; ?>/registro" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label for="name">Nombre: <sup>*</sup></label>
                <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
                <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email: <sup>*</sup></label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="location">Ubicación (Ciudad): <sup>*</sup></label>
                <input type="text" name="location" class="form-control <?php echo (!empty($data['location_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['location']; ?>" placeholder="Ej: Santiago, Viña del Mar, Remoto">
                <span class="invalid-feedback"><?php echo $data['location_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="role">Tipo de Cuenta: <sup>*</sup></label>
                <select name="role" class="form-control">
                    <option value="user">Candidato (Busco empleo)</option>
                    <option value="recruiter">Reclutador (Busco talento)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Contraseña: <sup>*</sup></label>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña: <sup>*</sup></label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['confirm_password']; ?>">
                <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
            </div>

            <div class="form-actions">
                <input type="submit" value="Registrarse" class="btn-main btn-block">
                <div class="form-actions__link">
                    <a class="link-muted" href="<?php echo URLROOT; ?>/login">¿Ya tienes cuenta? Ingresa</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
