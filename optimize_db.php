<?php
require_once 'app/config/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Índices para búsquedas de ubicación en empleos
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_location ON jobs (location)");
    
    // Índices para likes de trabajos
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_job_likes_user ON job_likes (user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_job_likes_job ON job_likes (job_id)");
    
    // Índices para likes de candidatos
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidate_likes_recruiter ON candidate_likes (recruiter_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidate_likes_candidate ON candidate_likes (candidate_id)");

    echo "Base de datos optimizada: índices creados con éxito.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
