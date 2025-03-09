<?php
require_once '../config/db_config.php';

if(isset($_GET['query'])) {
    $query = $_GET['query'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM personajes WHERE nombre LIKE :query ORDER BY id DESC");
        $stmt->execute(['query' => '%'.$query.'%']);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Error en la búsqueda: " . $e->getMessage());
    }
} else {
    // Si no se envía la variable "query", redirige a la lista de personajes
    header("Location: leer_personajes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de búsqueda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap y Font Awesome (si se requieren para los íconos o estilos) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Resultados de búsqueda para "<?php echo htmlspecialchars($query); ?>"</h1>
        <?php if(!empty($resultados)): ?>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Tipo</th>
                    <th>Nivel</th>
                    <th>Foto</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($resultados as $personaje): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($personaje['id']); ?></td>
                      <td><?php echo htmlspecialchars($personaje['nombre']); ?></td>
                      <td><?php echo htmlspecialchars($personaje['color']); ?></td>
                      <td><?php echo htmlspecialchars($personaje['tipo']); ?></td>
                      <td><?php echo htmlspecialchars($personaje['nivel']); ?></td>
                      <td>
                        <?php if(!empty($personaje['foto'])): ?>
                          <img src="<?php echo htmlspecialchars($personaje['foto']); ?>" alt="Foto" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                        <?php else: ?>
                          <span>No disponible</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="editar_personaje.php?id=<?php echo $personaje['id']; ?>" class="btn btn-warning btn-sm">
                          <i class="fas fa-edit"></i> Editar
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?php echo $personaje['id']; ?>" data-endpoint="eliminar_personaje.php">
                          <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <a href="../pdf/descargar_pdf.php?id=<?php echo $personaje['id']; ?>" class="btn btn-info btn-sm">
                          <i class="fas fa-file-pdf"></i> PDF
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        <?php else: ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>
        <a href="leer_personajes.php" class="btn btn-secondary mt-3">Volver a la lista</a>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Si utilizas el mismo script de eliminación, inclúyelo aquí -->
</body>
</html>
