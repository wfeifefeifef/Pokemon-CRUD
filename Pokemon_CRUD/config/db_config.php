<?php
define('DB_FOLDER', __DIR__ . '/data');
define('DB_PATH', DB_FOLDER . '/Pokemon.db');

// Verificar si la carpeta 'data' existe, si no, crearla
if (!file_exists(DB_FOLDER)) {
    mkdir(DB_FOLDER, 0777, true); // Crear la carpeta con permisos adecuados
}

// Verificar si el archivo de la base de datos existe antes de conectarse
if (!file_exists(DB_PATH)) {
    // Crear un archivo vacío para evitar problemas de permisos
    file_put_contents(DB_PATH, '');
    chmod(DB_PATH, 0666); // Ajustar permisos para permitir escritura
}

try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
