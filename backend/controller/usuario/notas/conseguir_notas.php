<?php
require_once "../../../config/database.php";

$id = isset($_GET['idUserx']) ? trim($_GET['idUserx']) : '';



// Consulta para obtener las notas
$query = "SELECT
    n.id_notas,
    n.titulo,
    n.contenido,
    n.etiqueta,
    n.recordatorio,
    n.id_usuario,
    n.esta_pineada,
    (SELECT GROUP_CONCAT(ne.descripcion_etiqueta SEPARATOR ', ')
     FROM notas_etiquetas ne
     WHERE ne.id_nota = n.id_notas) AS etiqueta_extra
FROM notas n
LEFT JOIN collaboradores c ON n.id_notas = c.id_nota
WHERE n.id_usuario = $id OR c.id_usuario = $id
ORDER BY
    CASE
        WHEN n.esta_pineada = 'si' THEN 0
        ELSE 1
    END,
    n.id_notas DESC;";

$stmt = $pdo->query($query);
$notas = $stmt->fetchAll();

// Procesar cada nota
foreach ($notas as &$nota) {
    if (!empty($nota["recordatorio"])) {
        $nota["recordatorio"] = date("Y-m-d\TH:i", strtotime($nota["recordatorio"]));
    }
    $id_nota = $nota["id_notas"];
    // Consulta para obtener los colaboradores
    $collabQuery = "SELECT u.nombre, u.apellido FROM collaboradores c 
                    JOIN empleados u ON c.id_usuario = u.id_empleado
                    WHERE c.id_nota = :id_nota";

    $stmtCollab = $pdo->prepare($collabQuery);
    $stmtCollab->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
    $stmtCollab->execute();
    $nota["colaboradores"] = $stmtCollab->fetchAll();
}

// Convertir a JSON y enviar la respuesta
echo json_encode($notas);
?>
