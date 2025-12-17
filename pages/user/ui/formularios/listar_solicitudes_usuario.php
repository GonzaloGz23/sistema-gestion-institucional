<?php
include '../../../../backend/config/database.php';

$idUsuario = $_POST['id_empleado'] ?? null;

if (!$idUsuario) {
    exit;
}

$sqlSolicitudes = $pdo->prepare("
    SELECT rs.id_solicitud_rh, rs.fecha_solicitud, rs.respuesta AS respuesta_rrhh,
           f.nombre AS nombre_formulario, f.id_formularios
    FROM rrhh_solicitudes rs
    INNER JOIN formularios f ON f.id_formularios = rs.id_formulario
    WHERE rs.id_empleado = ?
    ORDER BY rs.fecha_solicitud DESC
");
$sqlSolicitudes->execute([$idUsuario]);
$solicitudes = $sqlSolicitudes->fetchAll();

if (count($solicitudes) === 0) {
    echo '<div class="col-12"><p class="text-muted">No hay solicitudes hechas hasta el momento.</p></div>';
} else {
    foreach ($solicitudes as $s) {
        echo '
            <div class="col-lg-3 col-md-4 col-sm-12 mb-3">
                <div class="card card-hover">
                    <div class="card-body">
                        <div class="mb-2 text-muted small">
                            <i class="bi bi-calendar3 me-1"></i>
                            ' . date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) . '
                        </div>
                        <h5 class="text-truncate">' . htmlspecialchars($s['nombre_formulario']) . '</h5>

                        <div class="d-flex justify-content-end align-items-center">
                            <input type="hidden" class="id_solicitud_rh" value="' . $s['id_solicitud_rh'] . '">
                            <input type="hidden" class="id_form" value="' . $s['id_formularios'] . '">
                            <input type="hidden" class="fecha" value="' . $s['fecha_solicitud'] . '">
                            <input type="hidden" class="id_empleado" value="' . $idUsuario . '">
                            <button class="btn btn-outline-secondary button_ver_solicitud_usuario">
                                Ver más
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }
}
