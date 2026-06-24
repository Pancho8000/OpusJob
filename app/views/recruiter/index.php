<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="container">
    <div class="hero">
        
        <div id="recruiter-card-stack" class="card-demo">
            <div id="recruiter-card-content" class="card-content">
                <div class="loading">Buscando candidatos...</div>
            </div>
            
            <button id="btn-info-recruiter" class="btn-fab" type="button" title="Ver perfil completo" aria-label="Ver perfil completo">
                <i class="fas fa-info-circle"></i>
            </button>

            <div class="actions">
                <button id="btn-nope-recruiter" class="btn btn-round btn-nope" type="button" aria-label="Descartar candidato"><i class="fas fa-times"></i></button>
                <button id="btn-like-recruiter" class="btn btn-round btn-like btn-like-recruiter" type="button" aria-label="Guardar candidato"><i class="fas fa-check"></i></button>
            </div>
        </div>
    </div>
</div>

<div id="recruiter-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal-recruiter close-modal">&times;</span>
        <h2 id="modal-title">Nombre del Candidato</h2>
        <h4 id="modal-subtitle" class="modal-subtitle">Profesión</h4>
        <div class="modal-body">
            <p><strong>Sobre mí:</strong> <span id="modal-desc">Biografía...</span></p>
            <p><strong>Expectativa Salarial:</strong> <span id="modal-salary"></span></p>
            <p><strong>Ubicación:</strong> <span id="modal-location"></span></p>
        </div>
    </div>
</div>

<script>
    const jobsData = <?php echo isset($data['jobs']) ? json_encode($data['jobs']) : '[]'; ?>;
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
