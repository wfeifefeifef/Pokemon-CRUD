<?php
// Incluir configuración de la base de datos
require_once __DIR__ . '/config/db_config.php';

// Ruta al archivo de base de datos
$dbFile = __DIR__ . '/config/data/Pokemon.db';

// Verificar si la base de datos ya existe
if (file_exists($dbFile)) {
    echo "<h2>La instalación ya ha sido completada.</h2>";
    echo "<p>La base de datos ya se encuentra configurada.</p>";
    exit;
}

// Si no existe la carpeta "data" dentro de config, se crea
if (!file_exists(__DIR__ . '/config/data')) {
    mkdir(__DIR__ . '/config/data', 0777, true);
}

try {
    // Conexión a la base de datos SQLite
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sentencia SQL para crear la tabla "personajes"
    $sql = "CREATE TABLE IF NOT EXISTS personajes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre VARCHAR(100) NOT NULL,
        color VARCHAR(50),
        tipo VARCHAR(50),
        nivel INTEGER,
        foto VARCHAR(255)
    );";

    // Ejecutar la creación de la tabla
    $pdo->exec($sql);

    // Mensaje de confirmación
    echo "<h2>Instalación completada con éxito.</h2>";
    echo "<p>La base de datos y la tabla <strong>personajes</strong> han sido creadas correctamente.</p>";

} catch (PDOException $e) {
    // En caso de error, se muestra el mensaje correspondiente
    echo "<h2>Error durante la instalación:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
