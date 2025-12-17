<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

header('Content-Type: application/json');

$id_usuariox = isset($_GET['idUser']) ? trim($_GET['idUser']) : null; // Permitir null

if (isset($_GET['p'])) {
    $idcarpetaPadre = $_GET['p'];

    // Breadcrumbs dinámicos
    $stmt = $pdo->prepare("SELECT
        c_hijo.id_carpeta AS id_carpeta_hijo,
        c_padre.id_carpeta AS id_carpeta_padre,
        c_abuelo.id_carpeta AS id_carpeta_abuelo,
        c_hijo.nombre_carpeta AS nombre_carpeta_hijo,
        c_padre.nombre_carpeta AS nombre_carpeta_padre,
        c_abuelo.nombre_carpeta AS nombre_carpeta_abuelo
        FROM carpeta c_hijo
        LEFT JOIN subcarpeta sc_hijo ON c_hijo.id_carpeta = sc_hijo.carpeta_hijo
        LEFT JOIN carpeta c_padre ON sc_hijo.carpeta_padre = c_padre.id_carpeta
        LEFT JOIN subcarpeta sc_padre ON c_padre.id_carpeta = sc_padre.carpeta_hijo
        LEFT JOIN carpeta c_abuelo ON sc_padre.carpeta_padre = c_abuelo.id_carpeta
        WHERE c_hijo.id_carpeta = :idcarpetaPadre");

    $stmt->bindParam(':idcarpetaPadre', $idcarpetaPadre, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    function breadcrumbLink($id, $name)
    {
        $trunc = strlen($name) > 8 ? substr($name, 0, 8) . '...' : $name;
        return '<a href="administradorArchivos.php?p=' . $id . '" class="text-dark text-decoration-none me-2" data-bs-toggle="tooltip" title="' . htmlspecialchars($name) . '">' . htmlspecialchars($trunc) . '</a>';
    }

    $breadcrumbs = ''; // Inicializar la variable de breadcrumbs

    if ($resultado) {
        $breadcrumbs .= '<a href="administradorArchivos.php" class="text-dark text-decoration-none me-2">Inicio</a>';
        if ($resultado['nombre_carpeta_abuelo'])
            $breadcrumbs .= '<i class="bi bi-chevron-right mx-1"></i>' . breadcrumbLink($resultado['id_carpeta_abuelo'], $resultado['nombre_carpeta_abuelo']);
        if ($resultado['nombre_carpeta_padre'])
            $breadcrumbs .= '<i class="bi bi-chevron-right mx-1"></i>' . breadcrumbLink($resultado['id_carpeta_padre'], $resultado['nombre_carpeta_padre']);
        $breadcrumbs .= '<i class="bi bi-chevron-right mx-1"></i><span class="fw-bold">' . htmlspecialchars($resultado['nombre_carpeta_hijo']) . '</span>';
    }

    echo json_encode(['breadcrumbs' => $breadcrumbs]); // Enviar breadcrumbs como JSON
    exit; // Importante para evitar salida adicional
} else {
    // Si no hay parámetro 'p', puedes enviar una respuesta JSON vacía o un mensaje de error
    echo json_encode(['breadcrumbs' => '']); // O un mensaje de error: ['error' => 'Parámetro p no proporcionado']
    exit;
}
?>