<?php
class Matches extends Controller {
    private $userModel;

    public function __construct(){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/login');
            exit;
        }
        $this->userModel = $this->model('User');
    }

    private function requireRole($role){
        if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role){
            $this->json(403, ['ok' => false]);
        }
    }

    private function requireCsrf(){
        $token = null;
        if(isset($_POST['csrf_token'])){
            $token = $_POST['csrf_token'];
        } elseif(isset($_SERVER['HTTP_X_CSRF_TOKEN'])){
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if(!$token || !verify_csrf_token($token)){
            $this->json(403, ['ok' => false]);
        }
    }

    public function index(){
        $data = [
            'title' => 'Mis Matches',
            'description' => 'Tus empleos guardados'
        ];
        
        $this->view('matches/index', $data);
    }

    public function likeJob(){
        $this->requireRole('user');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $this->requireCsrf();

        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        if(!$jobId){
            $this->json(400, ['ok' => false]);
        }

        $this->userModel->likeJob((int)$_SESSION['user_id'], (int)$jobId);
        $this->json(200, ['ok' => true]);
    }

    public function clearJobs(){
        $this->requireRole('user');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $this->requireCsrf();
        $this->userModel->clearLikedJobs((int)$_SESSION['user_id']);
        $this->json(200, ['ok' => true]);
    }

    public function removeJob(){
        $this->requireRole('user');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $this->requireCsrf();
        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        if(!$jobId){
            $this->json(400, ['ok' => false]);
        }
        $ok = $this->userModel->removeLikedJob((int)$_SESSION['user_id'], (int)$jobId);
        $this->json(200, ['ok' => (bool)$ok]);
    }

    public function listJobs(){
        $this->requireRole('user');
        $jobs = $this->userModel->getLikedJobs((int)$_SESSION['user_id']);
        foreach($jobs as $job){
            if(!isset($job->icon)){
                $job->icon = 'fa-briefcase';
            }
        }
        $this->json(200, ['ok' => true, 'data' => $jobs]);
    }

    public function likeCandidate(){
        $this->requireRole('recruiter');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $this->requireCsrf();

        $candidateId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
        if(!$candidateId){
            $this->json(400, ['ok' => false]);
        }

        $this->userModel->likeCandidate((int)$_SESSION['user_id'], (int)$candidateId);
        $this->json(200, ['ok' => true]);
    }

    public function clearCandidates(){
        $this->requireRole('recruiter');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->json(405, ['ok' => false]);
        }
        $this->requireCsrf();
        $this->userModel->clearLikedCandidates((int)$_SESSION['user_id']);
        $this->json(200, ['ok' => true]);
    }

    public function listCandidates(){
        $this->requireRole('recruiter');
        $candidates = $this->userModel->getLikedCandidates((int)$_SESSION['user_id']);

        $mapped = [];
        foreach($candidates as $candidate){
            $mapped[] = [
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

        $this->json(200, ['ok' => true, 'data' => $mapped]);
    }
}
