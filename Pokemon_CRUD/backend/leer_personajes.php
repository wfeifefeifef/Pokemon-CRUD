<?php
require_once '../config/db_config.php';

// Si se envía una búsqueda, se filtran los resultados; de lo contrario, se muestran todos.
if(isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = trim($_GET['query']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM personajes WHERE nombre LIKE :query ORDER BY id DESC");
        $stmt->execute(['query' => '%' . $query . '%']);
        $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Error al filtrar la base de datos: " . $e->getMessage());
    }
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM personajes ORDER BY id DESC");
        $stmt->execute();
        $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Error al leer la base de datos: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Personajes - Dashboard Kortex</title>
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
    table { width: 100%; }
    /* Estilo para el backdrop del modal */
    .modal-backdrop { backdrop-filter: blur(5px); }
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
      <h1>Lista de Personajes Pokémon</h1>
      <div class="d-flex justify-content-between align-items-center mb-3">
          <a href="crear_personaje.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar nuevo personaje
          </a>
          <!-- Formulario de búsqueda -->
          <form class="d-flex" action="leer_personajes.php" method="GET">
            <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Buscar" name="query" value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>">
            <button class="btn btn-dark" type="submit">Buscar</button>
          </form>
      </div>
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
            <?php if(!empty($personajes)): ?>
              <?php foreach ($personajes as $p): ?>
                <tr id="row-<?php echo $p['id']; ?>">
                  <td><?php echo htmlspecialchars($p['id']); ?></td>
                  <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($p['color']); ?></td>
                  <td><?php echo htmlspecialchars($p['tipo']); ?></td>
                  <td><?php echo htmlspecialchars($p['nivel']); ?></td>
                  <td>
                    <?php if (!empty($p['foto'])): ?>
                      <img src="<?php echo htmlspecialchars($p['foto']); ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                      <span>No disponible</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="editar_personaje.php?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">
                      <i class="fas fa-edit"></i> Editar
                    </a>
                    <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?php echo $p['id']; ?>" data-endpoint="eliminar_personaje.php">
                      <i class="fas fa-trash"></i> Eliminar
                    </button>
                    <a href="../pdf/descargar_pdf.php?id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                      <i class="fas fa-file-pdf"></i> PDF
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center">No se encontraron personajes.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Modal de confirmación para eliminar -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">
            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro que deseas eliminar este personaje?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="button" class="btn btn-danger" id="confirmDelete">
            <i class="fas fa-trash"></i> Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script>
    // Toggle del menú de hamburguesa para móviles
    document.getElementById('hamburger-btn').addEventListener('click', function() {
      var sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    });
    
    // Script para manejo del modal y eliminación vía AJAX
    document.addEventListener('DOMContentLoaded', function() {
      var deleteId = null;
      var deleteEndpoint = null;
      var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
      
      document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function() {
          deleteId = this.getAttribute('data-id');
          deleteEndpoint = this.getAttribute('data-endpoint');
          deleteModal.show();
        });
      });
      
      document.getElementById('confirmDelete').addEventListener('click', function() {
        if(deleteId && deleteEndpoint) {
          fetch(deleteEndpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(deleteId)
          })
          .then(response => response.json())
          .then(data => {
            if(data.success) {
              var row = document.getElementById('row-' + deleteId);
              if(row) {
                row.parentNode.removeChild(row);
              }
              deleteModal.hide();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            alert('Error en la solicitud.');
          });
        }
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
