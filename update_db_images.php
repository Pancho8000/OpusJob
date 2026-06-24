<?php
// Script para actualizar la estructura de la base de datos
// Agrega campo de imagen y actualiza datos de prueba

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pegatinder_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");

    // Agregar columna 'image' si no existe
    $columns = $pdo->query("SHOW COLUMNS FROM jobs LIKE 'image'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN image VARCHAR(255) DEFAULT 'default.jpg' AFTER title");
        echo "Columna 'image' agregada a la tabla 'jobs'.<br>";
    }

    // Actualizar registros existentes con imágenes de prueba (placeholders)
    // Usaremos imágenes de Unsplash o similar para simular fotos de perfil/empresa
    $updates = [
        1 => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80', // Dev
        2 => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80', // Designer
        3 => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80', // Data
        4 => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80', // Junior
        5 => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80', // Backend
    ];

    $stmt = $pdo->prepare("UPDATE jobs SET image = ? WHERE id = ?");
    
    foreach ($updates as $id => $url) {
        $stmt->execute([$url, $id]);
    }
    
    echo "Imágenes actualizadas correctamente.";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
