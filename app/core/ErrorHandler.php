<?php
class ErrorHandler {
    public static function register(){
        set_error_handler([self::class, 'onError']);
        set_exception_handler([self::class, 'onException']);
    }

    public static function onError($severity, $message, $file, $line){
        if(!(error_reporting() & $severity)){
            return false;
        }

        // No lanzar excepción para errores menores (deprecaciones, avisos, etc)
        // a menos que queramos ser estrictos. Solo logueamos.
        $minorErrors = [E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE, E_USER_NOTICE, E_WARNING, E_USER_WARNING];
        
        if(in_array($severity, $minorErrors)){
            self::logThrowable('php_minor_error', $message, $file, $line, $severity);
            return true; // Continúa la ejecución
        }

        self::logThrowable('php_error', $message, $file, $line, $severity);
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function onException($e){
        self::logException($e);

        $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $debug = defined('DEBUG_MODE') && DEBUG_MODE;

        if($isJson || $isAjax){
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            $response = ['ok' => false, 'error' => 'server_error'];
            if($debug){
                $response['details'] = $e->getMessage();
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        
        if($debug){
            echo "<h1>Error del Servidor (Debug)</h1>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Archivo:</strong> " . $e->getFile() . " en línea " . $e->getLine() . "</p>";
            echo "<h3>Stack Trace:</h3><pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            echo '<h1>Ha ocurrido un error inesperado</h1>';
            echo '<p>Nuestro equipo técnico ha sido notificado. Por favor, intenta de nuevo más tarde.</p>';
        }
        exit;
    }

    private static function logException($e){
        $context = [
            'type' => get_class($e),
            'code' => (int)$e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        Logger::error($e->getMessage(), $context);
    }

    private static function logThrowable($type, $message, $file, $line, $severity = null){
        $context = [
            'type' => $type,
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        Logger::error($message, $context);
    }
}
