 <?php
 require_once "../../../config/session_config.php";
 require_once "../../../config/database.php";
 if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$idNota = $_POST['id_nota'] ?? null;

$stmt = $pdo->prepare("
    SELECT id_nota, titulo, contenido, tipo, pineada, fecha_creacion
    FROM notas WHERE id_nota = ?
");
$stmt->execute([$idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

// Tareas si es lista
$tareas = [];
if ($nota['tipo'] === 'lista') {
    $stmt2 = $pdo->prepare("SELECT id_lista_tarea, tarea, list_check FROM lista_tareas WHERE id_nota = ?");
    $stmt2->execute([$idNota]);
    $tareas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

// Colaboradores
$stmt3 = $pdo->prepare("
    SELECT e.id_empleado, e.nombre, e.apellido
    FROM colaboradores c
    JOIN empleados e ON e.id_empleado = c.id_usuario
    WHERE c.id_nota = ?
");
$stmt3->execute([$idNota]);
$colaboradores = $stmt3->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "nota" => $nota,
    "tareas" => $tareas,
    "colaboradores" => $colaboradores
]);
?>