<?php
// index.php
require_once 'config/db_config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM personajes ORDER BY id DESC");
    $stmt->execute();
    $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error al leer la base de datos: " . $e->getMessage());
}

// Cálculos de estadísticas generales
$totalPersonajes = count($personajes);
$niveles = array_column($personajes, 'nivel');
$promedioNivel = $totalPersonajes > 0 ? round(array_sum($niveles) / $totalPersonajes, 2) : 0;
$maxNivel = !empty($niveles) ? max($niveles) : 0;
$minNivel = !empty($niveles) ? min($niveles) : 0;

// Tipo más frecuente
$tipos = array_column($personajes, 'tipo');
$frecuencias = array_count_values($tipos);
arsort($frecuencias);
$tipoMasFrecuente = $totalPersonajes > 0 ? key($frecuencias) : 'N/A';

// Datos para el gráfico de distribución por tipo (Gráfico 1)
$tipoCounts = array_count_values($tipos);

// Datos para el gráfico de distribución por color (Gráfico 2)
$colores = array_column($personajes, 'color');
$colorCounts = array_count_values($colores);

// Datos para el gráfico de nivel promedio por tipo (Gráfico 3)
$nivelesPorTipo = [];
foreach($personajes as $p) {
    $tipo = $p['tipo'];
    if (!isset($nivelesPorTipo[$tipo])) {
        $nivelesPorTipo[$tipo] = ['sum' => 0, 'count' => 0];
    }
    $nivelesPorTipo[$tipo]['sum'] += $p['nivel'];
    $nivelesPorTipo[$tipo]['count']++;
}
$promedioPorTipo = [];
foreach($nivelesPorTipo as $tipo => $data) {
    $promedioPorTipo[$tipo] = $data['count'] > 0 ? round($data['sum'] / $data['count'], 2) : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Kortex - Gráficos y Estadísticas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap y Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    /* Sidebar visible por defecto en pantallas grandes */
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
      /* Cambio de color al pasar el mouse */
      color: #ffcc00; 
    }
    /* Contenido */
    .content { margin-left: 270px; padding: 20px; }
    @media (max-width: 768px) {
      .content { margin-left: 0; padding-top: 60px; }
    }
    /* Contenedor de gráficos pequeños */
    .chart-container {
      width: 250px;
      height: 250px;
      margin: auto;
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
      <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
      <a href="backend/leer_personajes.php"><i class="fas fa-list"></i> Lista de Personajes</a>
      <a href="backend/crear_personaje.php"><i class="fas fa-plus-circle"></i> Agregar Personaje</a>
  </div>
  
  <!-- Contenido principal -->
  <div class="content">
    <div class="container-fluid">
      <h1>Dashboard - Gráficos y Estadísticas</h1>
      
      <!-- Card con los 3 gráficos -->
      <div class="card shadow mb-4" style="background-color: #e7f3fe;">
        <div class="card-body">
          <div class="row justify-content-center">
            <div class="col-auto text-center">
              <div class="chart-container shadow p-3 mb-3 bg-white rounded">
                <canvas id="chartTipo"></canvas>
              </div>
              <p class="mt-2">Distribución por Tipo</p>
            </div>
            <div class="col-auto text-center">
              <div class="chart-container shadow p-3 mb-3 bg-white rounded">
                <canvas id="chartColor"></canvas>
              </div>
              <p class="mt-2">Distribución por Color</p>
            </div>
            <div class="col-auto text-center">
              <div class="chart-container shadow p-3 mb-3 bg-white rounded">
                <canvas id="chartPromedioTipo"></canvas>
              </div>
              <p class="mt-2">Nivel Promedio por Tipo</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Sección de estadísticas generales -->
      <div class="row mt-4">
        <div class="col text-center">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="card-title">Total de Personajes</h5>
              <p class="card-text fs-4"><?php echo $totalPersonajes; ?></p>
            </div>
          </div>
        </div>
        <div class="col text-center">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="card-title">Promedio de Nivel</h5>
              <p class="card-text fs-4"><?php echo $promedioNivel; ?></p>
            </div>
          </div>
        </div>
        <div class="col text-center">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="card-title">Nivel Máximo</h5>
              <p class="card-text fs-4"><?php echo $maxNivel; ?></p>
            </div>
          </div>
        </div>
        <div class="col text-center">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="card-title">Nivel Mínimo</h5>
              <p class="card-text fs-4"><?php echo $minNivel; ?></p>
            </div>
          </div>
        </div>
        <div class="col text-center">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="card-title">Tipo Más Frecuente</h5>
              <p class="card-text fs-4"><?php echo $tipoMasFrecuente; ?></p>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </div>
  
  <!-- Inicialización de los gráficos y script para el menú hamburguesa -->
  <script>
    // Toggle del menú de hamburguesa
    document.getElementById('hamburger-btn').addEventListener('click', function() {
      var sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    });

    // Gráfico 1: Distribución por Tipo (Gráfico de pastel)
    var ctxTipo = document.getElementById('chartTipo').getContext('2d');
    var chartTipo = new Chart(ctxTipo, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_keys($tipoCounts)); ?>,
        datasets: [{
          data: <?php echo json_encode(array_values($tipoCounts)); ?>,
          backgroundColor: [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)'
          ]
        }]
      }
    });

    // Gráfico 2: Distribución por Color (Gráfico de dona)
    var ctxColor = document.getElementById('chartColor').getContext('2d');
    var chartColor = new Chart(ctxColor, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode(array_keys($colorCounts)); ?>,
        datasets: [{
          data: <?php echo json_encode(array_values($colorCounts)); ?>,
          backgroundColor: [
            'rgba(255, 159, 64, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)'
          ]
        }]
      }
    });

    // Gráfico 3: Nivel Promedio por Tipo (Gráfico de barras)
    var ctxPromedioTipo = document.getElementById('chartPromedioTipo').getContext('2d');
    var chartPromedioTipo = new Chart(ctxPromedioTipo, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_keys($promedioPorTipo)); ?>,
        datasets: [{
          label: 'Nivel Promedio',
          data: <?php echo json_encode(array_values($promedioPorTipo)); ?>,
          backgroundColor: 'rgba(75, 192, 192, 0.6)'
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
