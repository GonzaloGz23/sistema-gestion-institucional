<?php
include 'common/header.php';
// Verificar autenticación usando función helper
if (!verificarUsuarioAutenticado()) {
    header("Location: login/login.php");
    exit;
}
include 'common/navbar.php';
include 'common/sidebar.php';
/* require_once '../backend/config/database.php'; */

// Obtener datos del usuario de forma segura
$usuarioActual = obtenerUsuarioActual();
$idRol = $usuarioActual['id_rol'];
$stmt = $pdo->prepare("
SELECT m.nombre, m.ruta, m.icono_svg, m.perfil
    FROM modulos m
    INNER JOIN roles_modulos rm ON m.id_modulo = rm.id_modulo
    WHERE rm.id_rol = :idRol AND m.activo = 'Activo' and m.id_modulo_fk IS NULL
    ORDER BY m.perfil ASC, m.orden ASC
");
$stmt->execute([':idRol' => $idRol]);
$modulos = $stmt->fetchAll();

// Separar y ordenar módulos: primero los admin, luego los user
$modulosAdmin = array_filter($modulos, fn($m) => $m['perfil'] === 'admin');
$modulosUsuario = array_filter($modulos, fn($m) => $m['perfil'] === 'user');
// Unir los arrays para mostrar primero los admin
$todosModulos = array_merge($modulosAdmin, $modulosUsuario);
?>
<link rel="stylesheet" href="./common/css/index-custom.css">
<main class="db-content">
    <div class="container-fluid mb-4">
        <!-- Banner de encuesta -->
       <!--  <div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fe fe-clock fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>¡Semana de prueba completada!</strong> Es hora de completar la encuesta de uso.
                    <a href="./user/encuestaSatisfaccion.php" class="btn btn-warning btn-sm m-1">
                        <i class="fe fe-edit-2 me-1"></i>
                        Completar encuesta
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div> -->

        <?php if (empty($todosModulos)): ?>
            <div class="alert alert-warning mt-4">No tenés módulos asignados a tu rol.</div>
        <?php else: ?>
            <div class="contenedor-cards mt-4">
                <!-- Card estática externa -->
                <a href="https://sen.com.ar/Guia/" target="_blank" class="card card-acceso card-border-primary rounded-4 text-decoration-none position-relative">
                    <div class="icon-image card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                        <div class="icon-image">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-arrow-left-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5m14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5"/>
                            </svg>
                        </div>
                        <div><h6 class="mb-0">GUIA</h6></div>
                    </div>
                </a>
                
                <?php foreach ($todosModulos as $modulo): ?>
                    <?php
                    $rutaBase = $modulo['perfil'] === 'admin' ? './admin/' : './user/';
                    $rutaCompleta = $rutaBase . htmlspecialchars($modulo['ruta']);
                    $icono = $modulo['icono_svg']
                        ? ajustarTamanioSvg($modulo['icono_svg'], 50)
                        : '<i class="fe fe-tablet fs-1 text-primary"></i>';
                    ?>
                    <a href="<?= $rutaCompleta ?>" class="card card-acceso card-border-primary rounded-4 text-decoration-none position-relative">
                        <div class="icon-image card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                            <div class="icon-image"><?= $icono ?></div>
                            <div><h6 class="mb-0"><?= htmlspecialchars($modulo['nombre']) ?></h6></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include 'common/scripts.php'; ?>
<?php include 'common/footer.php'; ?>