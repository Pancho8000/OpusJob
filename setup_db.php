<?php
// Script de configuración inicial de la base de datos
// Ejecutar esto una vez para crear la BD y las tablas

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pegatinder_db';

try {
    // Conectar sin seleccionar DB para poder crearla
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos '$dbname' creada o ya existente.<br>";

    // Seleccionar la DB
    $pdo->exec("USE `$dbname`");

    // Crear tabla Jobs
    $sql_jobs = "CREATE TABLE IF NOT EXISTS `jobs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `company` varchar(255) NOT NULL,
        `location` varchar(255) NOT NULL,
        `salary` varchar(100) NOT NULL,
        `type` varchar(50) NOT NULL,
        `description` text,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_jobs);
    echo "Tabla 'jobs' creada.<br>";

    // Crear tabla Users (preparación para futuro)
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_users);
    echo "Tabla 'users' creada.<br>";

    $sql_job_likes = "CREATE TABLE IF NOT EXISTS `job_likes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `job_id` int(11) NOT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user_job` (`user_id`,`job_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_job_likes);
    echo "Tabla 'job_likes' creada.<br>";

    $sql_candidate_likes = "CREATE TABLE IF NOT EXISTS `candidate_likes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `recruiter_id` int(11) NOT NULL,
        `candidate_id` int(11) NOT NULL,
        `status` varchar(32) NOT NULL DEFAULT 'postulacion_recibida',
        `job_id` int(11) DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_recruiter_candidate` (`recruiter_id`,`candidate_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_candidate_likes);
    echo "Tabla 'candidate_likes' creada.<br>";

    // Insertar datos de prueba si la tabla jobs está vacía
    $stmt = $pdo->query("SELECT COUNT(*) FROM jobs");
    if ($stmt->fetchColumn() == 0) {
        $jobs = [
            ['Desarrollador Full Stack', 'Tech Solutions', 'Santiago, Remoto', '$1.800.000 - $2.500.000', 'Full Time', 'Buscamos desarrollador con experiencia en PHP, JS y MySQL.'],
            ['Diseñador UX/UI', 'Creative Studio', 'Viña del Mar', '$1.200.000 - $1.500.000', 'Híbrido', 'Experiencia en Figma y Adobe XD requerida.'],
            ['Analista de Datos', 'DataCorp', 'Santiago, Las Condes', '$1.500.000 - $2.000.000', 'Presencial', 'SQL avanzado y Python.'],
            ['Junior Frontend Dev', 'Startup Chile', 'Remoto', '$900.000 - $1.200.000', 'Full Time', 'React o Vue.js, ganas de aprender.'],
            ['Backend Developer', 'Fintech Latam', 'Santiago', '$2.200.000 - $3.000.000', 'Remoto', 'Node.js o Python, experiencia en APIs REST.'],
        ];

        $insert = $pdo->prepare("INSERT INTO jobs (title, company, location, salary, type, description) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($jobs as $job) {
            $insert->execute($job);
        }
        echo "Datos de prueba insertados en 'jobs'.<br>";
    } else {
        echo "La tabla 'jobs' ya tiene datos.<br>";
    }

    echo "Configuración completada exitosamente.";

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
