<?php
// Script para añadir columna location a users
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pegatinder_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Añadir columna location
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN location VARCHAR(255) DEFAULT 'Santiago' AFTER email");
        echo "Columna 'location' añadida a tabla 'users'.<br>";
    } catch (PDOException $e) {
        echo "Columna 'location' probablemente ya existe.<br>";
    }

    // Añadir columna avatar
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER location");
        echo "Columna 'avatar' añadida a tabla 'users'.<br>";
    } catch (PDOException $e) {
        echo "Columna 'avatar' probablemente ya existe.<br>";
    }

    // Actualizar usuarios existentes
    $pdo->exec("UPDATE users SET location = 'Santiago' WHERE location IS NULL OR location = ''");
    echo "Usuarios actualizados con ubicación por defecto 'Santiago'.<br>";

    // Crear un usuario de prueba en otra región para probar
    $password = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, location, password, role) VALUES (:name, :email, :location, :password, :role)");
    
    // Usuario en Viña
    try {
        $stmt->execute(['name' => 'Usuario Viña', 'email' => 'vina@test.com', 'location' => 'Viña del Mar', 'password' => $password, 'role' => 'user']);
        echo "Usuario creado: vina@test.com (Ubicación: Viña del Mar)<br>";
    } catch(PDOException $e) {
        // Ignorar si ya existe email unique
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
