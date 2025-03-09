<?php
require_once '../config/db_config.php';

// Verificar que se haya pasado el ID
if (!isset($_GET['id'])) {
    die("ID no especificado.");
}

$id = intval($_GET['id']);

// Recuperar datos del personaje
try {
    $stmt = $pdo->prepare("SELECT * FROM personajes WHERE id = ?");
    $stmt->execute([$id]);
    $personaje = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$personaje) {
        die("Personaje no encontrado.");
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$message = ""; // Variable para mensajes

// Procesar el formulario al enviarse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $color  = trim($_POST['color']);
    $tipo   = trim($_POST['tipo']);
    $nivel  = intval($_POST['nivel']);

    if(empty($nombre)) {
        $message = "<div class='alert alert-danger'>El nombre es obligatorio.</div>";
    } else {
        // Por defecto se mantiene la foto actual
        $fotoPath = $personaje['foto'];

        // Si se sube una nueva foto, se procesa y reemplaza la anterior
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $fileName    = $_FILES['foto']['name'];
            $fileSize    = $_FILES['foto']['size'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if (!in_array($fileExtension, $allowedExt)) {
                $message = "<div class='alert alert-danger'>Error: Extensión de archivo no permitida.</div>";
            } elseif ($fileSize > 2 * 1024 * 1024) {
                $message = "<div class='alert alert-danger'>Error: El archivo es muy grande. Tamaño máximo 2MB.</div>";
            } else {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/uploads/';
                if (!file_exists($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Elimina la foto anterior, si existe
                    if (!empty($fotoPath) && file_exists(__DIR__ . '/' . $fotoPath)) {
                        unlink(__DIR__ . '/' . $fotoPath);
                    }
                    $fotoPath = 'uploads/' . $newFileName;
                } else {
                    $message = "<div class='alert alert-danger'>Error al mover el archivo subido.</div>";
                }
            }
        }

        // Si no hay errores, actualizar el registro en la base de datos
        if (empty($message)) {
            try {
                $stmt = $pdo->prepare("UPDATE personajes SET nombre = ?, color = ?, tipo = ?, nivel = ?, foto = ? WHERE id = ?");
                $stmt->execute([$nombre, $color, $tipo, $nivel, $fotoPath, $id]);
                $message = "<div class='alert alert-success'>Personaje actualizado exitosamente.</div>";
                // Actualizamos el array $personaje para reflejar los cambios
                $personaje['nombre'] = $nombre;
                $personaje['color'] = $color;
                $personaje['tipo'] = $tipo;
                $personaje['nivel'] = $nivel;
                $personaje['foto'] = $fotoPath;
            } catch(PDOException $e) {
                $message = "<div class='alert alert-danger'>Error al actualizar: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Personaje - Dashboard Kortex</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap y Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <style>
    body { margin: 0; padding: 0; }
    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      width: 250px;
      background-color: #004d99;
      padding: 20px;
      color: #fff;
      transition: transform 0.3s ease;
      z-index: 1100;
    }
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.active {
        transform: translateX(0);
      }
    }
    .sidebar h2 { margin: 0 0 30px; text-align: center; font-size: 24px; }
    .sidebar a {
      color: #fff;
      text-decoration: none;
      margin-bottom: 15px;
      display: block;
      font-size: 18px;
      transition: color 0.3s ease;
    }
    .sidebar a:hover { 
      /* Cambio de color en hover */
      color: #ffcc00;
    }
    /* Contenido */
    .content { margin-left: 270px; padding: 20px; }
    @media (max-width: 768px) {
      .content { margin-left: 0; padding-top: 60px; }
    }
    /* Top Navbar para móviles */
    .top-navbar {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background-color: #004d99;
      padding: 10px;
      z-index: 1200;
    }
    @media (max-width: 768px) {
      .top-navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .top-navbar .title {
        color: #fff;
        font-size: 20px;
        margin: 0;
      }
    }
    /* Botón de hamburguesa */
    .hamburger {
      display: none;
      background: none;
      border: none;
      color: #fff;
      font-size: 24px;
      margin: 0 10px;
    }
    @media (max-width: 768px) {
      .hamburger {
        display: block;
      }
    }
  </style>
</head>
<body>
  <!-- Top Navbar visible en dispositivos móviles -->
  <div class="top-navbar">
    <button class="hamburger" id="hamburger-btn"><i class="fas fa-bars"></i></button>
    <h2 class="title"><i class="fas fa-dragon"></i> Kortex</h2>
  </div>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
      <h2><i class="fas fa-dragon"></i> Kortex</h2>
      <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
      <a href="leer_personajes.php"><i class="fas fa-list"></i> Lista de Personajes</a>
      <a href="crear_personaje.php"><i class="fas fa-plus-circle"></i> Agregar Personaje</a>
  </div>
  <!-- Contenido principal -->
  <div class="content">
    <div class="container-fluid">
      <h1>Editar Personaje</h1>
      <?php
        if (!empty($message)) {
            echo $message;
            // En caso de éxito, se muestra un botón para volver a la lista
            if (strpos($message, 'exitosamente') !== false) {
                echo "<p><a href='leer_personajes.php' class='btn btn-primary'><i class='fas fa-list'></i> Volver a la lista</a></p>";
            }
        }
      ?>
      <form action="editar_personaje.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($personaje['nombre']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="color" class="form-label">Color:</label>
            <input type="text" name="color" id="color" class="form-control" value="<?php echo htmlspecialchars($personaje['color']); ?>">
          </div>
          <div class="mb-3">
            <label for="tipo" class="form-label">Tipo:</label>
            <input type="text" name="tipo" id="tipo" class="form-control" value="<?php echo htmlspecialchars($personaje['tipo']); ?>">
          </div>
          <div class="mb-3">
            <label for="nivel" class="form-label">Nivel:</label>
            <input type="number" name="nivel" id="nivel" class="form-control" value="<?php echo htmlspecialchars($personaje['nivel']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="foto" class="form-label">Foto (dejar en blanco para mantener la actual):</label>
            <input type="file" name="foto" id="foto" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            <?php if (!empty($personaje['foto'])): ?>
              <p class="mt-2">Foto actual:</p>
              <img src="<?php echo htmlspecialchars($personaje['foto']); ?>" alt="Foto Actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
            <?php endif; ?>
          </div>
          <button type="submit" class="btn btn-success"><i class="fas fa-edit"></i> Actualizar</button>
      </form>
    </div>
  </div>
  
  <script>
    // Toggle del menú de hamburguesa para dispositivos móviles
    document.getElementById('hamburger-btn').addEventListener('click', function() {
      var sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
