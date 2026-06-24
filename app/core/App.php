<?php
/*
 * App Core Class
 * Crea URL y carga el controlador core
 * URL FORMAT - /controller/method/params
 */
class App {
    protected $currentController = 'Home';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct(){
        //print_r($this->getUrl());

        $url = $this->getUrl();
        $url = self::resolveAliases($url);

        // Buscar controlador en controllers
        if(isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . '.php')){
            // Si existe, setear como controlador
            $this->currentController = ucwords($url[0]);
            // Unset 0 index
            unset($url[0]);
        }

        // Requerir el controlador
        require_once '../app/controllers/' . $this->currentController . '.php';

        // Instanciar la clase controlador
        $this->currentController = new $this->currentController;

        // Chequear segundo parte de url (método)
        if(isset($url[1])){
            if(method_exists($this->currentController, $url[1])){
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Obtener params
        $this->params = $url ? array_values($url) : [];

        // Llamar callback con array de params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl(){
        if(isset($_GET['url'])){
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }

    public static function resolveAliases($url){
        if(!is_array($url) || !isset($url[0])){
            return is_array($url) ? $url : [];
        }

        $slug = strtolower($url[0]);

        $fixed = [
            'login' => ['Users', 'login'],
            'registro' => ['Users', 'register'],
            'register' => ['Users', 'register'],
            'mi-perfil' => ['Users', 'me'],
            'perfil' => ['Users', 'me'],
            'editar-perfil' => ['Users', 'profile'],
            'crear-oferta' => ['Recruiter', 'createJob'],
            'mis-ofertas' => ['Recruiter', 'myJobs'],
            'reclutamiento' => ['Recruiter', 'pipeline'],
        ];

        $controllerOnly = [
            'empleos' => 'Home',
            'candidatos' => 'Recruiter',
            'guardados' => 'Matches',
        ];

        if($slug === 'reclutador'){
            $url[0] = 'Users';
            if(isset($url[1]) && $url[1] !== ''){
                $url[2] = $url[1];
            }
            $url[1] = 'public';
            return $url;
        }

        if(isset($fixed[$slug])){
            $url[0] = $fixed[$slug][0];
            $url[1] = $fixed[$slug][1];
            return $url;
        }

        if(isset($controllerOnly[$slug])) {
            $url[0] = $controllerOnly[$slug];
            if(!isset($url[1]) || $url[1] === ''){
                $url[1] = 'index';
            }
        }

        return $url;
    }
}
