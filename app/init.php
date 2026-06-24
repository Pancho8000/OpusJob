<?php
require_once 'config/config.php';
require_once 'helpers/session_helper.php';
require_once 'helpers/security_helper.php';

// Autoload Core Libraries
spl_autoload_register(function($className){
    require_once 'core/' . $className . '.php';
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ErrorHandler::register();
