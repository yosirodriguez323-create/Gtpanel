<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar autenticación (básica por ahora)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Panel de Administración';
$current_page = 'dashboard';

// Obtener estadísticas rápidas
try {
    $stats = [
        'mangas' => $pdo->query("SELECT COUNT(*) FROM mangas")->fetchColumn(),
        'animes' => $pdo->query("SELECT COUNT(*) FROM animes")->fetchColumn(),
        'peliculas' => $pdo->query("SELECT COUNT(*) FROM peliculas")->fetchColumn(),
        'series' => $pdo->query("SELECT COUNT(*) FROM series")->fetchColumn(),
        'descargas_hoy' => $pdo->query("SELECT COUNT(*) FROM descargas WHERE DATE(fecha_descarga) = CURDATE()")->fetchColumn()
    ];
} catch(PDOException $e) {
    $stats = ['mangas' => 0, 'animes' => 0, 'peliculas' => 0, 'series' => 0, 'descargas_hoy' => 0];
}

include 'includes/header.php';
?>

<div class="admin-container">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h1>Panel de Administración</h1>
            <div class="user-info">
                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card manga">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3>Mangas</h3>
                    <p class="stat-number"><?php echo $stats['mangas']; ?></p>
                </div>
                <a href="modules/mangas/" class="stat-link">Gestionar</a>
            </div>

            <div class="stat-card anime">
                <div class="stat-icon">🎬</div>
                <div class="stat-info">
                    <h3>Animes</h3>
                    <p class="stat-number"><?php echo $stats['animes']; ?></p>
                </div>
                <a href="modules/animes/" class="stat-link">Gestionar</a>
            </div>

            <div class="stat-card pelicula">
                <div class="stat-icon">🎥</div>
                <div class="stat-info">
                    <h3>Películas</h3>
                    <p class="stat-number"><?php echo $stats['peliculas']; ?></p>
                </div>
                <a href="modules/peliculas/" class="stat-link">Gestionar</a>
            </div>

            <div class="stat-card serie">
                <div class="stat-icon">📺</div>
                <div class="stat-info">
                    <h3>Series</h3>
                    <p class="stat-number"><?php echo $stats['series']; ?></p>
                </div>
                <a href="modules/series/" class="stat-link">Gestionar</a>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="actions-grid">
                <a href="modules/mangas/agregar.php" class="action-btn">
                    <span class="icon">➕</span>
                    Agregar Manga
                </a>
                <a href="modules/animes/agregar.php" class="action-btn">
                    <span class="icon">➕</span>
                    Agregar Anime
                </a>
                <a href="modules/peliculas/agregar.php" class="action-btn">
                    <span class="icon">➕</span>
                    Agregar Película
                </a>
                <a href="modules/series/agregar.php" class="action-btn">
                    <span class="icon">➕</span>
                    Agregar Serie
                </a>
                <a href="reportes.php" class="action-btn">
                    <span class="icon">📊</span>
                    Ver Reportes
                </a>
                <a href="configuracion.php" class="action-btn">
                    <span class="icon">⚙️</span>
                    Configuración
                </a>
            </div>
        </div>

        <!-- Últimos Contenidos -->
        <div class="recent-content">
            <h2>Contenido Reciente</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Unión de últimas actualizaciones de todas las tablas
                            $sql = "(SELECT 'Manga' as tipo, titulo, fecha_creacion, estado, id FROM mangas)
                                    UNION ALL
                                    (SELECT 'Anime' as tipo, titulo, fecha_creacion, estado, id FROM animes)
                                    UNION ALL
                                    (SELECT 'Película' as tipo, titulo, fecha_creacion, estado, id FROM peliculas)
                                    UNION ALL
                                    (SELECT 'Serie' as tipo, titulo, fecha_creacion, estado, id FROM series)
                                    ORDER BY fecha_creacion DESC LIMIT 10";
                            
                            $stmt = $pdo->query($sql);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $estado_class = $row['estado'] == 'activo' ? 'status-active' : 'status-inactive';
                                echo "<tr>";
                                echo "<td><span class='type-badge type-{$row['tipo']}'>{$row['tipo']}</span></td>";
                                echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['fecha_creacion'])) . "</td>";
                                echo "<td><span class='{$estado_class}'>" . ucfirst($row['estado']) . "</span></td>";
                                echo "<td>
                                    <a href='modules/" . strtolower($row['tipo']) . "s/editar.php?id={$row['id']}' class='btn-edit'>✏️</a>
                                    <a href='modules/" . strtolower($row['tipo']) . "s/eliminar.php?id={$row['id']}' class='btn-delete' onclick='return confirm(\"¿Estás seguro?\")'>🗑️</a>
                                </td>";
                                echo "</tr>";
                            }
                        } catch(PDOException $e) {
                            echo "<tr><td colspan='5'>Error al cargar datos</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
