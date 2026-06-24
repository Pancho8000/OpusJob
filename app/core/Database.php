<?php
/*
 * PDO Database Class
 * Conectar a la base de datos
 * Crear prepared statements
 * Vincular valores
 * Retornar filas y resultados
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;
    private $lastSql;
    private $lastStart;

    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Crear instancia PDO
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            // Fix caracteres latinos
            $this->dbh->exec('set names utf8');
        } catch(PDOException $e){
            $this->error = $e->getMessage();
            Logger::error('db_connect_failed', ['error' => $this->error]);
            throw $e;
        }
    }

    // Preparar statement
    public function query($sql){
        $this->lastSql = $sql;
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincular valores
    public function bind($param, $value, $type = null){
        if(is_null($type)){
            switch(true){
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecutar prepared statement
    public function execute(){
        $this->lastStart = microtime(true);
        try{
            $result = $this->stmt->execute();
        } catch(PDOException $e){
            Logger::error('db_query_failed', ['sql' => $this->lastSql, 'error' => $e->getMessage()]);
            throw $e;
        }

        $elapsedMs = (microtime(true) - $this->lastStart) * 1000.0;
        if($elapsedMs >= 200){
            Logger::warning('db_slow_query', ['sql' => $this->lastSql, 'ms' => (int)$elapsedMs]);
        }

        return $result;
    }

    // Obtener conjunto de resultados como array de objetos
    public function resultSet(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Obtener un solo registro como objeto
    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Obtener row count
    public function rowCount(){
        return $this->stmt->rowCount();
    }
}
