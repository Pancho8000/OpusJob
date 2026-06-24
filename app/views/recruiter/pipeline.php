<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="page-center">
    <div class="panel pipeline-panel">
        <h2>Reclutamiento</h2>
        <p>Gestiona candidatos guardados y asígnalos a una vacante con estado de avance.</p>

        <div id="recruiter-pipeline">
            <div class="pipeline-filters">
                <div class="form-group">
                    <label for="pipeline-job" class="label-strong">Puesto postulado</label>
                    <select id="pipeline-job" class="form-control">
                        <option value="">Todos</option>
                        <?php if(isset($data['jobs']) && is_array($data['jobs'])): ?>
                            <?php foreach($data['jobs'] as $job): ?>
                                <option value="<?php echo (int)$job->id; ?>"><?php echo esc($job->title . ' — ' . $job->company); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pipeline-status" class="label-strong">Estado</label>
                    <select id="pipeline-status" class="form-control">
                        <option value="">Todos</option>
                        <option value="postulacion_recibida">Postulación recibida</option>
                        <option value="entrevista">En proceso de entrevista</option>
                        <option value="seleccionado">Seleccionado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
            </div>

            <div class="pipeline-filters pipeline-filters--secondary">
                <div class="form-group">
                    <label for="pipeline-q" class="label-strong">Filtrar por habilidades / experiencia / bio</label>
                    <input id="pipeline-q" class="form-control" type="text" placeholder="Ej: PHP, React, UX, SQL...">
                    <div class="help-text">Busca en habilidades, profesión y biografía.</div>
                </div>

                <div class="form-group">
                    <label for="pipeline-min-exp" class="label-strong">Experiencia mínima (años)</label>
                    <input id="pipeline-min-exp" class="form-control" type="number" min="0" max="50" value="0">
                </div>
            </div>

            <div class="pipeline-actions">
                <button id="pipeline-apply" class="btn btn-primary" type="button"><i class="fas fa-filter"></i> Aplicar filtros</button>
            </div>

            <div id="pipeline-empty" class="pipeline-empty" style="display:none;">No hay postulantes o candidatos en proceso para tus ofertas.</div>
            <div id="pipeline-list" class="pipeline-grid"></div>
        </div>
    </div>
</div>

<script>
    window.RECRUITER_JOBS = <?php echo json_encode($data['jobs'] ?? [], JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
