<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="container">
    <div class="hero">
        <p id="jobs-counter" class="jobs-counter"></p>

        <div id="card-stack" class="card-demo">
            <div id="card-content" class="card-content">
                <div class="loading">Cargando empleos...</div>
            </div>
            
            <button id="btn-info" class="btn-fab" type="button" title="Ver más detalles" aria-label="Ver más detalles">
                <i class="fas fa-info-circle"></i>
            </button>

            <div class="actions">
                <button id="btn-nope" class="btn btn-round btn-nope" type="button" aria-label="Descartar empleo"><i class="fas fa-times"></i></button>
                <button id="btn-like" class="btn btn-round btn-like" type="button" aria-label="Guardar empleo"><i class="fas fa-heart"></i></button>
            </div>
        </div>
    </div>
</div>

<div id="job-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modal-title">Título del Trabajo</h2>
        <h4 id="modal-company">Empresa</h4>
        <div class="modal-body">
            <p><strong>Descripción:</strong> <span id="modal-desc">Aquí va la descripción detallada del puesto...</span></p>
            <p><strong>Salario:</strong> <span id="modal-salary"></span></p>
            <p><strong>Ubicación:</strong> <span id="modal-location"></span></p>
        </div>
    </div>
</div>

<script>
    const jobsData = <?php echo isset($data['jobs']) ? json_encode($data['jobs']) : '[]'; ?>;
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
