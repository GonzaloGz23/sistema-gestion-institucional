<?php
// Incluir configuración de sesión y validar usuario
include '../../../../backend/config/database.php';

// Obtener datos del usuario de forma segura
$id_nota=$_POST['id_nota'];

$stmt = $pdo->prepare("
   SELECT e.nombre nombre, e.id_empleado id, e.apellido apellido
FROM `collaboradores`  c
LEFT JOIN empleados e on c.id_usuario= e.id_empleado
WHERE c.id_nota =?  and c.estado=1
");
$stmt->execute([$id_nota]);

$usuarios = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $usuarios[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'apellido' => $row['apellido'],
        'siglas' => strtoupper(substr($row['nombre'], 0, 1) . substr($row['apellido'], 0, 1))
    ];
}

header('Content-Type: application/json');
echo json_encode($usuarios);

