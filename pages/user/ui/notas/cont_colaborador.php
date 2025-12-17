<?php
include '../../../../backend/config/database.php';
header('Content-Type: application/json');

$idNota = $_GET['id'] ?? null;
/* if (!$idNota) {
    echo '<div>Error: ID de nota no definido</div>';
    exit;
} */

// Colaboradores asignados activos para esta nota
$stmt = $pdo->prepare("
    SELECT e.id_empleado, e.nombre, e.apellido 
    FROM collaboradores c 
    INNER JOIN empleados e ON e.id_empleado = c.id_usuario
    WHERE c.id_nota = ? AND c.estado = 1
    ORDER BY e.nombre ASC
");
$stmt->execute([$idNota]);
$colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Empleados disponibles para agregar (no asignados o asignados pero estado = 0)
$stmt2 = $pdo->prepare("
    SELECT id_empleado, nombre, apellido 
    FROM empleados 
    WHERE id_empleado NOT IN (
        SELECT id_usuario FROM collaboradores WHERE id_nota = ? AND estado = 1
    )
    AND borrado = 0 AND estado = 'habilitado' 
    ORDER BY nombre ASC
");
$stmt2->execute([$idNota]);
$disponibles = $stmt2->fetchAll(PDO::FETCH_ASSOC);


?>

<!-- Select para agregar colaboradores -->
<div class="mb-3">
    <label for="nuevo_colaborador" class="form-label fw-bold">Agregar nuevo colaborador:</label>
    <select id="nuevo_colaborador" class="form-select" data-nota="<?= htmlspecialchars($idNota) ?>">
        <option value="">Seleccionar colaborador</option>
        <?php foreach ($disponibles as $empleado): ?>
            <option value="<?= $empleado['id_empleado'] ?>">
                <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Lista de colaboradores asignados -->
 <div class="mb-3">
    <label class="form-label fw-bold">Colaboradores asignados:</label>
    <div id="lista-colaboradores" class="list-group">
        <?php if (empty($colaboradores)): ?>
            <div class="text-muted px-2 py-1">No hay colaboradores asignados.</div>
        <?php else: ?>
            <?php foreach ($colaboradores as $colab): 
                $siglas = strtoupper(substr($colab['nombre'], 0, 1) . substr($colab['apellido'], 0, 1));
            ?>
                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="<?= $colab['id_empleado'] ?>">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="avatar avatar-sm rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <?= $siglas ?>
                            </span>
                        </div>
                        <div>
                            <small><?= htmlspecialchars($colab['nombre'] . ' ' . $colab['apellido']) ?></small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger btn-eliminar-colaborador" data-id="<?= $colab['id_empleado'] ?>" data-nota="<?= htmlspecialchars($idNota) ?>">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

