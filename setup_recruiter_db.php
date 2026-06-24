<?php
// Script para configurar la tabla de candidatos (Vista de Reclutador)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pegatinder_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla Candidates
    $sql_candidates = "CREATE TABLE IF NOT EXISTS `candidates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `profession` varchar(255) NOT NULL,
        `location` varchar(255) NOT NULL,
        `salary_expectation` varchar(100) NOT NULL,
        `skills` varchar(255) NOT NULL,
        `bio` text,
        `image` varchar(255) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(50) DEFAULT NULL,
        `cv_url` varchar(255) DEFAULT NULL,
        `years_experience` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql_candidates);
    echo "Tabla 'candidates' creada.<br>";

    // Insertar datos de prueba
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidates");
    if ($stmt->fetchColumn() == 0) {
        $candidates = [
            [
                'Juan Pérez', 
                'Desarrollador Full Stack', 
                'Santiago', 
                '$2.000.000', 
                'PHP, Laravel, Vue.js', 
                'Apasionado por el código limpio y arquitecturas escalables. 5 años de experiencia.',
                'https://randomuser.me/api/portraits/men/32.jpg'
            ],
            [
                'María González', 
                'Diseñadora UX/UI', 
                'Viña del Mar', 
                '$1.400.000', 
                'Figma, Adobe XD, CSS', 
                'Me enfoco en crear experiencias de usuario intuitivas y atractivas.',
                'https://randomuser.me/api/portraits/women/44.jpg'
            ],
            [
                'Carlos Rodríguez', 
                'Data Scientist', 
                'Remoto', 
                '$2.500.000', 
                'Python, SQL, Machine Learning', 
                'Experto en análisis de datos y modelos predictivos para negocios.',
                'https://randomuser.me/api/portraits/men/85.jpg'
            ],
            [
                'Ana López', 
                'Frontend Developer', 
                'Santiago', 
                '$1.200.000', 
                'React, Tailwind, TypeScript', 
                'Desarrolladora frontend con ojo para el detalle y el diseño responsive.',
                'https://randomuser.me/api/portraits/women/65.jpg'
            ],
            [
                'Pedro Sánchez', 
                'Backend Developer', 
                'Concepción', 
                '$1.800.000', 
                'Node.js, Express, MongoDB', 
                'Especialista en APIs RESTful y microservicios de alto rendimiento.',
                'https://randomuser.me/api/portraits/men/12.jpg'
            ]
        ];

        $insert = $pdo->prepare("INSERT INTO candidates (name, profession, location, salary_expectation, skills, bio, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($candidates as $candidate) {
            $insert->execute($candidate);
        }
        echo "Datos de prueba insertados en 'candidates'.<br>";
    } else {
        echo "La tabla 'candidates' ya tiene datos.<br>";
    }

    echo "Configuración de Reclutador completada exitosamente.";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
