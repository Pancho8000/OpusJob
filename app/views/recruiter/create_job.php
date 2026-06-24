<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="page-center">
    <div class="panel" style="max-width: 800px; width: 100%;">
        <h2><?php echo $data['title']; ?></h2>
        <p><?php echo $data['description']; ?></p>

        <form id="create-job-form" class="mt-20">
            <?php $job = $data['job'] ?? null; ?>
            <input type="hidden" id="id" name="id" value="<?php echo $job ? (int)$job->id : ''; ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label for="title" class="label-strong">Título del puesto *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Ej: Senior PHP Developer" value="<?php echo $job ? esc($job->title) : ''; ?>">
                    <span class="invalid-feedback" id="err-title"></span>
                </div>
                <div class="form-group">
                    <label for="location" class="label-strong">Ubicación *</label>
                    <select id="location" name="location" class="form-control">
                        <option value="" <?php echo (!$job || empty($job->location)) ? 'selected' : ''; ?>>Seleccione...</option>
                        <option value="Remoto" <?php echo ($job && $job->location === 'Remoto') ? 'selected' : ''; ?>>Remoto</option>
                        <option value="Híbrido" <?php echo ($job && $job->location === 'Híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                        <option value="Presencial" <?php echo ($job && $job->location === 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                    </select>
                    <span class="invalid-feedback" id="err-location"></span>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="type" class="label-strong">Tipo de jornada *</label>
                    <select id="type" name="type" class="form-control">
                        <option value="" <?php echo (!$job || empty($job->type)) ? 'selected' : ''; ?>>Seleccione...</option>
                        <option value="Full Time" <?php echo ($job && $job->type === 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo ($job && $job->type === 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                        <option value="Freelance" <?php echo ($job && $job->type === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                        <option value="Por Proyecto" <?php echo ($job && $job->type === 'Por Proyecto') ? 'selected' : ''; ?>>Por Proyecto</option>
                    </select>
                    <span class="invalid-feedback" id="err-type"></span>
                </div>
                <div class="form-group">
                    <label for="salary" class="label-strong">Rango salarial</label>
                    <input type="text" id="salary" name="salary" class="form-control" placeholder="Ej: $1.500.000 - $2.000.000" value="<?php echo $job ? esc($job->salary) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="label-strong">Descripción detallada *</label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe las responsabilidades del puesto..."><?php echo $job ? esc($job->description) : ''; ?></textarea>
                <span class="invalid-feedback" id="err-description"></span>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="requirements_tech" class="label-strong">Requisitos técnicos</label>
                    <textarea id="requirements_tech" name="requirements_tech" class="form-control" rows="3" placeholder="Ej: PHP 8, MySQL, AWS..."><?php echo $job ? esc($job->requirements_tech ?? '') : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="requirements_soft" class="label-strong">Habilidades blandas</label>
                    <textarea id="requirements_soft" name="requirements_soft" class="form-control" rows="3" placeholder="Ej: Liderazgo, comunicación..."><?php echo $job ? esc($job->requirements_soft ?? '') : ''; ?></textarea>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="benefits" class="label-strong">Beneficios</label>
                    <textarea id="benefits" name="benefits" class="form-control" rows="3" placeholder="Ej: Seguro complementario, bonos..."><?php echo $job ? esc($job->benefits ?? '') : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="deadline" class="label-strong">Fecha límite de postulación</label>
                    <input type="date" id="deadline" name="deadline" class="form-control" value="<?php echo $job ? esc($job->deadline ?? '') : ''; ?>">
                </div>
            </div>

            <div class="upload-row" style="justify-content: flex-end; gap: 10px; margin-top: 2rem;">
                <button type="button" id="btn-preview" class="btn btn-secondary">Previsualizar</button>
                <button type="button" id="btn-draft" class="btn btn-soft">Guardar Borrador</button>
                <button type="submit" id="btn-publish" class="btn btn-primary">Publicar Oferta</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Previsualización -->
<div id="preview-modal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <span class="close-modal">&times;</span>
            <h2 id="prev-title">Título de la Oferta</h2>
            <p id="prev-company" class="company--accent"><?php echo $_SESSION['user_name']; ?></p>
        </div>
        <div class="modal-body">
            <div class="details" style="justify-content: flex-start; margin-bottom: 1rem;">
                <span class="badge" id="prev-location"></span>
                <span class="badge" id="prev-type"></span>
                <span class="badge" id="prev-salary"></span>
            </div>
            
            <div class="mt-16">
                <h4 class="label-strong">Descripción</h4>
                <p id="prev-description" style="white-space: pre-wrap;"></p>
            </div>

            <div class="grid-2 mt-16">
                <div>
                    <h4 class="label-strong">Requisitos Técnicos</h4>
                    <p id="prev-tech" style="white-space: pre-wrap; font-size: 0.9rem;"></p>
                </div>
                <div>
                    <h4 class="label-strong">Habilidades Blandas</h4>
                    <p id="prev-soft" style="white-space: pre-wrap; font-size: 0.9rem;"></p>
                </div>
            </div>

            <div class="mt-16">
                <h4 class="label-strong">Beneficios</h4>
                <p id="prev-benefits" style="white-space: pre-wrap; font-size: 0.9rem;"></p>
            </div>

            <div class="mt-16 sidebar-muted" style="font-size: 0.8rem;">
                Fecha límite: <span id="prev-deadline"></span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('create-job-form');
    const modal = document.getElementById('preview-modal');
    const closeBtn = modal.querySelector('.close-modal');
    const api = window.Api;

    // Validación en tiempo real
    const validateField = (id) => {
        const field = document.getElementById(id);
        const err = document.getElementById(`err-${id}`);
        if(!err) return true;

        if(!field.value.trim()) {
            field.style.borderColor = 'var(--nope)';
            err.innerText = 'Este campo es obligatorio';
            return false;
        } else {
            field.style.borderColor = 'rgba(255,255,255,0.14)';
            err.innerText = '';
            return true;
        }
    };

    ['title', 'location', 'type', 'description'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => validateField(id));
        document.getElementById(id).addEventListener('blur', () => validateField(id));
    });

    // Previsualización
    document.getElementById('btn-preview').addEventListener('click', () => {
        document.getElementById('prev-title').innerText = document.getElementById('title').value || 'Sin título';
        document.getElementById('prev-location').innerHTML = `<i class="fas fa-map-marker-alt"></i> ${document.getElementById('location').value || '—'}`;
        document.getElementById('prev-type').innerHTML = `<i class="fas fa-clock"></i> ${document.getElementById('type').value || '—'}`;
        document.getElementById('prev-salary').innerHTML = `<i class="fas fa-money-bill-wave"></i> ${document.getElementById('salary').value || 'No especificado'}`;
        document.getElementById('prev-description').innerText = document.getElementById('description').value || 'Sin descripción';
        document.getElementById('prev-tech').innerText = document.getElementById('requirements_tech').value || '—';
        document.getElementById('prev-soft').innerText = document.getElementById('requirements_soft').value || '—';
        document.getElementById('prev-benefits').innerText = document.getElementById('benefits').value || '—';
        document.getElementById('prev-deadline').innerText = document.getElementById('deadline').value || 'Sin fecha';

        modal.style.display = 'block';
    });

    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if(e.target == modal) modal.style.display = 'none'; };

    // Envío del formulario
    const submitForm = async (status) => {
        const isTitleOk = validateField('title');
        const isLocOk = validateField('location');
        const isTypeOk = validateField('type');
        const isDescOk = validateField('description');

        if(!isTitleOk || !isLocOk || !isTypeOk || !isDescOk) {
            alert('Por favor, complete los campos obligatorios');
            return;
        }

        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        data.status = status;

        const res = await api.postForm('/Recruiter/saveJob', data);
        if(res && res.ok) {
            alert(status === 'published' ? '¡Oferta publicada exitosamente!' : 'Borrador guardado');
            localStorage.setItem('recruiter_jobs_refresh', Date.now().toString());
            window.location.href = window.URLROOT + (status === 'published' ? '/mis-ofertas' : '/mis-ofertas');
        } else {
            const errJson = await res.json();
            alert('Error: ' + (errJson.msg || 'No se pudo guardar la oferta'));
        }
    };

    form.onsubmit = (e) => {
        e.preventDefault();
        submitForm('published');
    };

    document.getElementById('btn-draft').addEventListener('click', () => submitForm('draft'));
});
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>
