<?php
/*
 * Base Controller
 * Carga modelos y vistas
 */
class Controller {
    // Cargar modelo
    public function model($model){
        // Requerir archivo de modelo
        require_once '../app/models/' . $model . '.php';
        // Instanciar modelo
        return new $model();
    }

    // Cargar vista
    public function view($view, $data = []){
        // Chequear si el archivo vista existe
        if(file_exists('../app/views/' . $view . '.php')){
            require_once '../app/views/' . $view . '.php';
        } else {
            // Vista no existe
            throw new Exception('La vista no existe');
        }
    }

    public function json($statusCode, $payload){
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
