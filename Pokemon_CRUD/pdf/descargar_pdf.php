<?php
// Incluir la configuración de la base de datos y el autoload de Composer
require_once '../config/db_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar que se haya pasado el ID del personaje
if (!isset($_GET['id'])) {
    die("<h2 style='text-align: center; color: red;'>ID no especificado.</h2>");
}
$id = intval($_GET['id']);

// Recuperar la información del personaje
try {
    $stmt = $pdo->prepare("SELECT * FROM personajes WHERE id = ?");
    $stmt->execute([$id]);
    $personaje = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$personaje) {
        die("<h2 style='text-align: center; color: red;'>Personaje no encontrado.</h2>");
    }
} catch(PDOException $e) {
    die("<h2 style='text-align: center; color: red;'>Error al obtener datos: " . $e->getMessage() . "</h2>");
}

// Determinar la ruta de la imagen
if (!empty($personaje['foto'])) {
    if (strpos($personaje['foto'], 'backend/') === 0) {
        $ruta_imagen = __DIR__ . '/../' . $personaje['foto'];
    } else {
        $ruta_imagen = __DIR__ . '/../backend/' . $personaje['foto'];
    }
} else {
    $ruta_imagen = '';
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil del Personaje</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .header {
            background-color: #004d99;
            color: #fff;
            padding: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .content {
            margin: 20px auto;
            width: 90%;
            max-width: 600px;
        }
        .perfil {
            border: 2px solid #004d99;
            padding: 20px;
            border-radius: 10px;
            background-color: #fff;
        }
        .perfil img {
            display: block;
            margin: 0 auto 20px auto;
            max-width: 150px;
            border-radius: 50%;
        }
        .perfil h2 {
            margin: 10px 0;
            font-size: 28px;
            color: #333;
        }
        .datos {
            font-size: 18px;
            color: #555;
        }
        .datos p {
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icono {
            font-size: 22px;
            margin-right: 10px;
            color: #004d99;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kortex</h1>
    </div>
    <div class="content">
        <div class="perfil">';

// Incrustar la imagen como base64 si existe y es accesible
if (!empty($ruta_imagen) && file_exists($ruta_imagen)) {
    $fileExtension = pathinfo($ruta_imagen, PATHINFO_EXTENSION);
    $imgData = base64_encode(file_get_contents($ruta_imagen));
    $src = 'data:image/' . $fileExtension . ';base64,' . $imgData;
    $html .= '<img src="' . $src . '" alt="Foto del personaje">';
} else {
    $html .= '<p style="text-align:center;">No hay imagen disponible.</p>';
}

$html .= '
            <h2>' . htmlspecialchars($personaje['nombre']) . '</h2>
            <div class="datos">
                <p><strong>Color Representativo:</strong> ' . htmlspecialchars($personaje['color']) . '</p>
                <p><strong>Tipo:</strong> ' . htmlspecialchars($personaje['tipo']) . '</p>
                <p><strong>Nivel:</strong> ' . htmlspecialchars($personaje['nivel']) . '</p>
            </div>
        </div>
    </div>
</body>
</html>';

// Configurar las opciones de Dompdf utilizando la clase Options
$options = new Options();
$options->setIsRemoteEnabled(true);

// Instanciar Dompdf con las opciones configuradas y cargar el contenido HTML
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Configurar el tamaño del papel y la orientación (A4, vertical)
$dompdf->setPaper('A4', 'portrait');

// Renderizar el HTML a PDF
$dompdf->render();

// Enviar el PDF al navegador (Attachment false muestra el PDF en línea)
$dompdf->stream("perfil_personaje_$id.pdf", ["Attachment" => false]);
?>
