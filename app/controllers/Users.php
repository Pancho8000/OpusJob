<?php
class Users extends Controller {
    private $userModel;
    private $jobModel;

    public function __construct(){
        $this->userModel = $this->model('User');
        $this->jobModel = $this->model('Job');
    }

    public function me(){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/login');
            exit;
        }

        $user = $this->userModel->getUserById((int)$_SESSION['user_id']);
        if(!$user){
            header('location: ' . URLROOT);
            exit;
        }

        $data = [
            'mode' => 'view',
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'name' => $user->name,
            'location' => isset($user->location) ? $user->location : '',
            'avatar' => isset($user->avatar) ? $user->avatar : '',
            'password' => '',
            'confirm_password' => '',
            'name_err' => '',
            'location_err' => '',
            'avatar_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];

        $this->view('users/profile', $data);
    }

    public function profile(){
        if(!isLoggedIn()){
            header('location: ' . URLROOT . '/login');
            exit;
        }

        $user = $this->userModel->getUserById((int)$_SESSION['user_id']);
        if(!$user){
            header('location: ' . URLROOT);
            exit;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                Logger::warning('csrf_invalid', ['route' => 'users/profile', 'user_id' => $_SESSION['user_id'] ?? null]);
                http_response_code(403);
                exit;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'mode' => 'edit',
                'id' => (int)$_SESSION['user_id'],
                'email' => $user->email,
                'role' => $user->role,
                'name' => trim($_POST['name'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'avatar' => isset($user->avatar) ? $user->avatar : '',
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'name_err' => '',
                'location_err' => '',
                'avatar_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            if(empty($data['name'])){
                $data['name_err'] = 'Por favor ingrese nombre';
            } elseif(mb_strlen($data['name']) > 80){
                $data['name_err'] = 'Nombre demasiado largo';
            }

            if(empty($data['location'])){
                $data['location_err'] = 'Por favor ingrese su ubicación (ej: Santiago)';
            } elseif(mb_strlen($data['location']) > 120){
                $data['location_err'] = 'Ubicación demasiado larga';
            }

            if(isset($_FILES['avatar']) && is_array($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE){
                if($_FILES['avatar']['error'] !== UPLOAD_ERR_OK){
                    $data['avatar_err'] = 'No se pudo subir la imagen';
                } else {
                    $maxSize = 2 * 1024 * 1024;
                    if($_FILES['avatar']['size'] > $maxSize){
                        $data['avatar_err'] = 'La imagen supera el tamaño permitido (2MB)';
                    } else {
                        $info = @getimagesize($_FILES['avatar']['tmp_name']);
                        if(!$info || !isset($info['mime'])){
                            $data['avatar_err'] = 'Archivo inválido';
                        } else {
                            $mime = $info['mime'];
                            $ext = '';
                            if($mime === 'image/jpeg') $ext = 'jpg';
                            if($mime === 'image/png') $ext = 'png';
                            if($mime === 'image/webp') $ext = 'webp';
                            if($ext === ''){
                                $data['avatar_err'] = 'Formato no permitido (usa JPG, PNG o WEBP)';
                            } else {
                                $fileName = 'avatar_user_' . $data['id'] . '_' . time() . '.' . $ext;
                                $destPath = dirname(APPROOT) . '/public/img/' . $fileName;
                                if(!move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)){
                                    $data['avatar_err'] = 'No se pudo guardar la imagen';
                                } else {
                                    $data['avatar'] = $fileName;
                                }
                            }
                        }
                    }
                }
            }

            if(!empty($data['password'])){
                if(strlen($data['password']) < 6){
                    $data['password_err'] = 'Contraseña debe tener al menos 6 caracteres';
                }
                if(empty($data['confirm_password'])){
                    $data['confirm_password_err'] = 'Por favor confirme contraseña';
                } elseif($data['password'] != $data['confirm_password']){
                    $data['confirm_password_err'] = 'Contraseñas no coinciden';
                }
            }

            if(empty($data['name_err']) && empty($data['location_err']) && empty($data['avatar_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])){
                if(!empty($data['password'])){
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                } else {
                    $data['password'] = '';
                }

                if($this->userModel->updateProfile($data)){
                    $_SESSION['user_name'] = $data['name'];
                    $_SESSION['user_location'] = $data['location'];
                    $_SESSION['user_avatar'] = $data['avatar'];
                    flash('profile_success', 'Perfil actualizado correctamente');
                    header('location: ' . URLROOT . '/mi-perfil');
                    exit;
                } else {
                    throw new Exception('No se pudo actualizar el perfil');
                }
            } else {
                $this->view('users/profile', $data);
            }
        } else {
            $data = [
                'mode' => 'edit',
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $user->name,
                'location' => isset($user->location) ? $user->location : '',
                'avatar' => isset($user->avatar) ? $user->avatar : '',
                'password' => '',
                'confirm_password' => '',
                'name_err' => '',
                'location_err' => '',
                'avatar_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            $this->view('users/profile', $data);
        }
    }

    public function public($id = null){
        $id = $id !== null ? (int)$id : (int)($_GET['id'] ?? 0);
        if($id <= 0){
            header('location: ' . URLROOT);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if(!$user || !isset($user->role) || $user->role !== 'recruiter'){
            header('location: ' . URLROOT);
            exit;
        }

        $data = [
            'mode' => 'public',
            'id' => $user->id,
            'email' => '',
            'role' => $user->role,
            'name' => $user->name,
            'location' => isset($user->location) ? $user->location : '',
            'avatar' => isset($user->avatar) ? $user->avatar : '',
            'password' => '',
            'confirm_password' => '',
            'name_err' => '',
            'location_err' => '',
            'avatar_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];
        $this->view('users/profile', $data);
    }

    public function recruiterOffersData($id = null){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }
        $id = $id !== null ? (int)$id : (int)($_GET['id'] ?? 0);
        if($id <= 0){
            $this->json(400, ['ok' => false]);
        }
        $items = $this->jobModel->getPublicRecruiterJobsWithStats($id);
        $this->json(200, ['ok' => true, 'data' => $items]);
    }

    public function myAccountJobsData(){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            $this->json(405, ['ok' => false]);
        }
        if(!isLoggedIn()){
            $this->json(401, ['ok' => false]);
        }
        if(!isRecruiter()){
            $this->json(403, ['ok' => false]);
        }
        try{
            $claimed = $this->jobModel->claimOrphanJobsForAccount((int)$_SESSION['user_id'], (string)($_SESSION['user_name'] ?? ''));
            if($claimed > 0){
                Logger::info('job_claim_orphans', ['user_id' => (int)$_SESSION['user_id'], 'claimed' => (int)$claimed]);
            }
        } catch(Exception $e){
        }
        $items = $this->jobModel->getAccountJobsWithStats((int)$_SESSION['user_id']);
        $this->json(200, ['ok' => true, 'data' => $items]);
    }

    public function register(){
        // Check for POST
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                Logger::warning('csrf_invalid', ['route' => 'users/register']);
                http_response_code(403);
                exit;
            }
            // Process form
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => strtolower(trim($_POST['email'] ?? '')),
                'location' => trim($_POST['location'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'role' => trim($_POST['role'] ?? 'user'),
                'name_err' => '',
                'email_err' => '',
                'location_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validations
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese email';
            } elseif(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $data['email_err'] = 'Email inválido';
            } else {
                if($this->userModel->findUserByEmail($data['email'])){
                    $data['email_err'] = 'Email ya está registrado';
                }
            }

            if(empty($data['name'])){
                $data['name_err'] = 'Por favor ingrese nombre';
            } elseif(mb_strlen($data['name']) > 80){
                $data['name_err'] = 'Nombre demasiado largo';
            }

            if(empty($data['location'])){
                $data['location_err'] = 'Por favor ingrese su ubicación (ej: Santiago)';
            } elseif(mb_strlen($data['location']) > 120){
                $data['location_err'] = 'Ubicación demasiado larga';
            }

            if(empty($data['password'])){
                $data['password_err'] = 'Por favor ingrese contraseña';
            } elseif(strlen($data['password']) < 6){
                $data['password_err'] = 'Contraseña debe tener al menos 6 caracteres';
            }

            if(empty($data['confirm_password'])){
                $data['confirm_password_err'] = 'Por favor confirme contraseña';
            } else {
                if($data['password'] != $data['confirm_password']){
                    $data['confirm_password_err'] = 'Contraseñas no coinciden';
                }
            }

            // Make sure errors are empty
            if(empty($data['email_err']) && empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err']) && empty($data['location_err'])){
                if(!in_array($data['role'], ['user', 'recruiter'], true)){
                    $data['role'] = 'user';
                }
                // Validated
                
                // Hash Password
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // Register User
                if($this->userModel->register($data)){
                    flash('register_success', 'Estás registrado y puedes iniciar sesión');
                    header('location: ' . URLROOT . '/login');
                } else {
                    throw new Exception('No se pudo registrar el usuario');
                }

            } else {
                // Load view with errors
                $this->view('users/register', $data);
            }

        } else {
            // Init data
            $data = [
                'name' => '',
                'email' => '',
                'location' => '',
                'password' => '',
                'confirm_password' => '',
                'role' => 'user',
                'name_err' => '',
                'email_err' => '',
                'location_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            $this->view('users/register', $data);
        }
    }

    public function login(){
        // Check for POST
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                Logger::warning('csrf_invalid', ['route' => 'users/login']);
                http_response_code(403);
                exit;
            }
            // Process form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'email' => strtolower(trim($_POST['email'] ?? '')),
                'password' => trim($_POST['password'] ?? ''),
                'email_err' => '',
                'password_err' => '',      
            ];

            // Validate Email
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese email';
            } elseif(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $data['email_err'] = 'Email inválido';
            }

            // Validate Password
            if(empty($data['password'])){
                $data['password_err'] = 'Por favor ingrese contraseña';
            }

            // Check for user/email
            if($this->userModel->findUserByEmail($data['email'])){
                // User found
            } else {
                $data['email_err'] = 'Usuario no encontrado';
            }

            // Make sure errors are empty
            if(empty($data['email_err']) && empty($data['password_err'])){
                // Validated
                // Check and set logged in user
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);

                if($loggedInUser){
                    // Create Session
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Contraseña incorrecta';
                    $this->view('users/login', $data);
                }
            } else {
                // Load view with errors
                $this->view('users/login', $data);
            }


        } else {
            // Init data
            $data = [    
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => '',        
            ];

            $this->view('users/login', $data);
        }
    }

    public function createUserSession($user){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_location'] = isset($user->location) ? $user->location : ''; // Guardar ubicación si existe
        $_SESSION['user_avatar'] = isset($user->avatar) ? $user->avatar : '';
        
        // Redireccionar según rol
        if($user->role == 'recruiter'){
            header('location: ' . URLROOT . '/candidatos');
        } else {
            header('location: ' . URLROOT . '/empleos');
        }
    }

    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_location']);
        unset($_SESSION['user_avatar']);
        session_destroy();
        header('location: ' . URLROOT . '/login');
    }
}
