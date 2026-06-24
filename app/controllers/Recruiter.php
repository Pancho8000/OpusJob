<?php
class Recruiter extends Controller {
    private $candidateModel;
    private $userModel;
    private $jobModel;

    public function __construct(){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/login');
            exit;
        }
        if(!isRecruiter()){
            header('location: ' . URLROOT);
            exit;
        }
        $this->candidateModel = $this->model('Candidate');
        $this->userModel = $this->model('User');
        $this->jobModel = $this->model('Job');
    }

    private function requireRecruiterRole(){
        if(!isRecruiter()){
            header('location: ' . URLROOT);
            exit;
        }
    }

    public function index(){
        $this->requireRecruiterRole();
        $candidates = $this->candidateModel->getCandidates(12, 0);

        // Mapear datos para que coincidan con lo que espera el JS (title, company, etc.)
        // O adaptar la vista. Vamos a adaptar los datos para reutilizar la lógica de JS.
        $mappedCandidates = [];
        foreach($candidates as $candidate){
            $mappedCandidates[] = [
                'id' => $candidate->id,
                'title' => $candidate->name, // Título principal -> Nombre del candidato
                'company' => $candidate->profession, // Subtítulo -> Profesión
                'location' => $candidate->location,
                'salary' => $candidate->salary_expectation,
                'type' => $candidate->skills,
                'description' => $candidate->bio,
                'image' => $candidate->image
            ];
        }

        $data = [
            'title' => 'Portal Reclutador',
            'description' => 'Encuentra al candidato ideal',
            'jobs' => $mappedCandidates // Pasamos 'jobs' porque el JS espera esa variable
        ];
        
        $this->view('recruiter/index', $data);
    }

    public function feed(){
        $this->requireRecruiterRole();
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }

        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
        if(!$limit || $limit < 1 || $limit > 50) $limit = 12;
        if($offset === false || $offset < 0) $offset = 0;

        $candidates = $this->candidateModel->getCandidates($limit, $offset);
        $mappedCandidates = [];
        foreach($candidates as $candidate){
            $mappedCandidates[] = [
                'id' => $candidate->id,
                'title' => $candidate->name,
                'company' => $candidate->profession,
                'location' => $candidate->location,
                'salary' => $candidate->salary_expectation,
                'type' => $candidate->skills,
                'description' => $candidate->bio,
                'image' => $candidate->image
            ];
        }
        $this->json(200, ['ok' => true, 'data' => $mappedCandidates]);
    }

    public function pipeline(){
        $this->requireRecruiterRole();
        $this->userModel->ensureRecruiterPipelineSchema();
        $this->userModel->ensureJobApplicationSchema();
        $jobs = $this->jobModel->getRecruiterJobs((int)$_SESSION['user_id']);
        $data = [
            'title' => 'Reclutamiento',
            'description' => 'Gestiona candidatos por vacante y etapa del proceso',
            'jobs' => $jobs
        ];
        $this->view('recruiter/pipeline', $data);
    }

    public function pipelineData(){
        $this->requireRecruiterRole();
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }

        $filters = [
            'job_id' => filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT),
            'status' => trim((string)($_GET['status'] ?? '')),
            'q' => trim((string)($_GET['q'] ?? '')),
            'min_exp' => filter_input(INPUT_GET, 'min_exp', FILTER_VALIDATE_INT),
        ];
        if($filters['min_exp'] === false || $filters['min_exp'] === null || $filters['min_exp'] < 0){
            $filters['min_exp'] = 0;
        }

        $this->userModel->ensureRecruiterPipelineSchema();
        $items = $this->userModel->getRecruiterPipeline((int)($_SESSION['user_id'] ?? 0), $filters);
        $filtersActive = !empty($filters['job_id']) || $filters['status'] !== '' || $filters['q'] !== '' || (int)$filters['min_exp'] > 0;
        $this->json(200, ['ok' => true, 'data' => $items, 'meta' => ['filters_active' => $filtersActive]]);
    }

    public function pipelineUpdate(){
        $this->requireRecruiterRole();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if(!$token || !verify_csrf_token($token)){
            $this->json(403, ['ok' => false]);
        }

        $candidateId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        $status = trim((string)($_POST['status'] ?? ''));

        if(!$candidateId){
            $this->json(400, ['ok' => false]);
        }

        $allowed = ['postulacion_recibida', 'entrevista', 'seleccionado', 'rechazado'];
        if(!in_array($status, $allowed, true)){
            $this->json(400, ['ok' => false]);
        }

        if($jobId === false || $jobId === null || $jobId <= 0){
            $jobId = null;
        }

        $this->userModel->ensureRecruiterPipelineSchema();
        $entryType = trim((string)($_POST['entry_type'] ?? 'candidate'));
        if(!in_array($entryType, ['candidate', 'job_applicant'], true)){
            $entryType = 'candidate';
        }
        $ok = $this->userModel->updateRecruiterPipeline((int)$_SESSION['user_id'], (int)$candidateId, $status, $jobId, $entryType);
        $this->json(200, ['ok' => (bool)$ok]);
    }

    public function createJob(){
        $jobId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $job = null;
        if($jobId){
            $job = $this->jobModel->getRecruiterJobById((int)$_SESSION['user_id'], (int)$jobId);
            if(!$job){
                header('location: ' . URLROOT . '/mis-ofertas');
                exit;
            }
        }
        $data = [
            'title' => 'Crear Oferta de Trabajo',
            'description' => 'Publica una nueva vacante para encontrar al mejor talento.',
            'job' => $job
        ];
        $this->view('recruiter/create_job', $data);
    }

    public function saveJob(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if(!$token || !verify_csrf_token($token)) $this->json(403, ['ok'=>false]);

        $data = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT),
            'title' => trim($_POST['title'] ?? ''),
            'company' => $_SESSION['user_name'], // Por ahora usamos el nombre del reclutador como empresa
            'location' => trim($_POST['location'] ?? ''),
            'salary' => trim($_POST['salary'] ?? ''),
            'type' => trim($_POST['type'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'requirements_tech' => trim($_POST['requirements_tech'] ?? ''),
            'requirements_soft' => trim($_POST['requirements_soft'] ?? ''),
            'benefits' => trim($_POST['benefits'] ?? ''),
            'deadline' => trim($_POST['deadline'] ?? ''),
            'status' => trim($_POST['status'] ?? 'published'),
            'recruiter_id' => (int)$_SESSION['user_id']
        ];

        // Validaciones básicas
        $errors = [];
        if(empty($data['title'])) $errors['title'] = 'El título es obligatorio';
        if(empty($data['location'])) $errors['location'] = 'La ubicación es obligatoria';
        if(empty($data['type'])) $errors['type'] = 'El tipo de jornada es obligatorio';
        if(empty($data['description'])) $errors['description'] = 'La descripción es obligatoria';

        if(!empty($errors)){
            $this->json(400, ['ok' => false, 'errors' => $errors]);
        }

        if($data['id']){
            $owned = $this->jobModel->getRecruiterJobById((int)$data['recruiter_id'], (int)$data['id']);
            if(!$owned){
                $this->json(403, ['ok' => false]);
            }
            $res = $this->jobModel->updateJob($data);
            $msg = 'Oferta actualizada';
        } else {
            $res = $this->jobModel->createJob($data);
            $msg = 'Oferta creada';
        }

        if($res){
            Logger::info('job_action', ['action' => $data['status'] === 'published' ? 'publish' : 'save_draft', 'recruiter_id' => $data['recruiter_id']]);
            $this->json(200, ['ok' => true, 'msg' => $msg]);
        } else {
            $this->json(500, ['ok' => false, 'msg' => 'Error al guardar en base de datos']);
        }
    }

    public function myJobs(){
        $data = [
            'title' => 'Mis ofertas publicadas',
            'description' => 'Administra tus publicaciones: activa, desactiva, edita o elimina.'
        ];
        $this->view('recruiter/my_jobs', $data);
    }

    public function myJobsData(){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }
        try{
            $this->jobModel->claimOrphanJobsForAccount((int)$_SESSION['user_id'], (string)($_SESSION['user_name'] ?? ''));
        } catch(Exception $e){
        }
        $items = $this->jobModel->getRecruiterJobsWithStats((int)$_SESSION['user_id']);
        $this->json(200, ['ok' => true, 'data' => $items]);
    }

    public function jobStatus(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if(!$token || !verify_csrf_token($token)){
            $this->json(403, ['ok' => false]);
        }
        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        $status = trim((string)($_POST['status'] ?? ''));
        if(!$jobId){
            $this->json(400, ['ok' => false]);
        }
        if(!in_array($status, ['draft', 'published'], true)){
            $this->json(400, ['ok' => false]);
        }
        $ok = $this->jobModel->setRecruiterJobStatus((int)$_SESSION['user_id'], (int)$jobId, $status);
        Logger::info('job_action', ['action' => $status === 'published' ? 'activate' : 'deactivate', 'recruiter_id' => (int)$_SESSION['user_id'], 'job_id' => (int)$jobId]);
        $this->json(200, ['ok' => (bool)$ok]);
    }

    public function deleteJob(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if(!$token || !verify_csrf_token($token)){
            $this->json(403, ['ok' => false]);
        }
        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        if(!$jobId){
            $this->json(400, ['ok' => false]);
        }
        $ok = $this->jobModel->deleteRecruiterJob((int)$_SESSION['user_id'], (int)$jobId);
        Logger::info('job_action', ['action' => 'delete', 'recruiter_id' => (int)$_SESSION['user_id'], 'job_id' => (int)$jobId]);
        $this->json(200, ['ok' => (bool)$ok]);
    }
}
