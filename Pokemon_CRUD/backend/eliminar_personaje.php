<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
    exit;
}

$id = intval($_POST['id']);

// Recuperar la ruta de la foto (si existe)
try {
    $stmt = $pdo->prepare("SELECT foto FROM personajes WHERE id = ?");
    $stmt->execute([$id]);
    $personaje = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$personaje) {
        echo json_encode(['success' => false, 'message' => 'Personaje no encontrado.']);
        exit;
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// Proceder a eliminar
try {
    $stmt = $pdo->prepare("DELETE FROM personajes WHERE id = ?");
    $stmt->execute([$id]);

    if (!empty($personaje['foto']) && file_exists(__DIR__ . '/' . $personaje['foto'])) {
        unlink(__DIR__ . '/' . $personaje['foto']);
    }
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>
