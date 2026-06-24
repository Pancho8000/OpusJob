<?php
class Home extends Controller {
    private $jobModel;

    public function __construct(){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/login');
            exit;
        }
        $this->jobModel = $this->model('Job');
    }

    public function index(){
        $location = isset($_SESSION['user_location']) ? $_SESSION['user_location'] : null;
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        $jobs = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user')
            ? $this->jobModel->getJobsForUser($userId, $location, 12, 0)
            : $this->jobModel->getJobs($location, 12, 0);

        // Agregar iconos si no existen en DB (lógica visual)
        foreach($jobs as $job){
            if(!isset($job->icon)){
                $job->icon = 'fa-briefcase'; // Icono por defecto
            }
        }

        $data = [
            'title' => 'PegaTinder',
            'description' => $location ? "Empleos cerca de $location" : 'Encuentra tu próximo empleo',
            'jobs' => $jobs
        ];
        
        $this->view('home/index', $data);
    }

    public function feed(){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }

        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
        if(!$limit || $limit < 1 || $limit > 50) $limit = 12;
        if($offset === false || $offset < 0) $offset = 0;

        $location = isset($_SESSION['user_location']) ? $_SESSION['user_location'] : null;
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $jobs = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user')
            ? $this->jobModel->getJobsForUser($userId, $location, $limit, $offset)
            : $this->jobModel->getJobs($location, $limit, $offset);
        foreach($jobs as $job){
            if(!isset($job->icon)){
                $job->icon = 'fa-briefcase';
            }
        }
        $this->json(200, ['ok' => true, 'data' => $jobs]);
    }
}
