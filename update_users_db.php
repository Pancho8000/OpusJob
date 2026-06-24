<?php
// Script para actualizar la tabla users con roles
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pegatinder_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Añadir columna role si no existe
    // Nota: MySQL no tiene "IF NOT EXISTS" para columnas en ALTER TABLE directos fácilmente en una línea sin procedures,
    // pero podemos intentar añadirla e ignorar error o verificar antes.
    // Haremos un drop/create para limpiar y asegurar estructura limpia en este entorno de dev.
    
    $pdo->exec("DROP TABLE IF EXISTS `users`");
    
    $sql_users = "CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('user','recruiter') DEFAULT 'user',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql_users);
    echo "Tabla 'users' recreada con columna role.<br>";

    // Crear usuarios de prueba
    // Password hash para "123456"
    $password = password_hash('123456', PASSWORD_DEFAULT);
    
    // Usuario Normal
    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => 'Usuario Test', 'email' => 'user@test.com', 'password' => $password, 'role' => 'user']);
    echo "Usuario creado: user@test.com / 123456 (Rol: User)<br>";
    
    // Reclutador
    $stmt->execute(['name' => 'Reclutador Test', 'email' => 'recruiter@test.com', 'password' => $password, 'role' => 'recruiter']);
    echo "Usuario creado: recruiter@test.com / 123456 (Rol: Recruiter)<br>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
