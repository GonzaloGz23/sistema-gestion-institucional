<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

header('Content-Type: application/json'); // <<--- ¡AGREGÁ ESTO!


$id_usuariox = $usuarioActual->id;
$id_equipox = $usuarioActual->id_equipo;

if (isset($_GET['p'])) {
    $idcarpetaPadre = $_GET['p'];

    // Subcarpetas
    $stmt = $pdo->prepare("SELECT c.id_carpeta, c.nombre_carpeta, c.rela_usuario, cc.nombre_colaborador,cc.apellido_colaborador FROM carpeta c INNER JOIN subcarpeta s ON c.id_carpeta = s.carpeta_hijo LEFT JOIN colaborador_carpetas cc ON c.id_carpeta = cc.id_carpeta WHERE s.carpeta_padre = :idcarpetaPadre AND c.visible = 'si' ");
    $stmt->bindParam(':idcarpetaPadre', $idcarpetaPadre, PDO::PARAM_INT);
    $stmt->execute();
    $subcarpetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subcarpetas as $c) {
        $nombreCorto = mb_substr($c['nombre_carpeta'], 0, 16);
        if (mb_strlen($c['nombre_carpeta']) > 16) {
            $nombreCorto .= "...";
        }
        $nombreCompleto = htmlspecialchars($c['nombre_carpeta']);
     $idCarpet = $c['id_carpeta'];

$carpetaRaizs= $pdo->prepare("WITH RECURSIVE FolderHierarchy AS (
    SELECT
        id_subcarpeta AS current_folder_id,
        carpeta_padre AS parent_folder_id
    FROM
        subcarpeta
    WHERE
        id_subcarpeta = $idCarpet -- Comienza desde la subcarpeta con ID 2

    UNION ALL

    SELECT
        s.id_subcarpeta AS current_folder_id,
        s.carpeta_padre AS parent_folder_id
    FROM
        subcarpeta s
    JOIN
        FolderHierarchy fh ON s.id_subcarpeta = fh.parent_folder_id
)
SELECT
    fh.current_folder_id AS raiz_carpeta_id
FROM
    FolderHierarchy fh
WHERE
    fh.parent_folder_id IS NULL; -- La condición para encontrar la raíz");



 $carpetaRaizs->execute();

            $idcarpetaraiz = $carpetaRaizs->fetchColumn();




    $stmtc = $pdo->prepare("SELECT COUNT(*) FROM colaborador_carpetas WHERE id_carpeta = :carpeta_id AND id_colaborador = :usuario_id");
            $stmtc->bindParam(':carpeta_id', $idcarpetaraiz, PDO::PARAM_INT);
            $stmtc->bindParam(':usuario_id', $id_usuariox, PDO::PARAM_INT);
            $stmtc->execute();

            $counts = $stmtc->fetchColumn();






        echo '
            <a href="administradorArchivos.php?p=' . $c['id_carpeta'] . '"  id="' . $counts . '"
                class="card text-center border-0 shadow-sm rounded p-0 cursor-pointer carpeta-item"
                data-id="' . $c['id_carpeta'] . '" 
                data-tipo="carpeta"
                data-nombre="' . $nombreCompleto . '"
                style="width: 7rem; height: 6.6rem; text-decoration: none;"
                data-bs-toggle="tooltip" data-bs-placement="top" title="' . $nombreCompleto . '">
                <div class="card-body d-flex flex-column justify-content-start align-items-center p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#334155" class="bi bi-folder-fill text-warning" viewBox="0 0 16 16">
                        <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"></path>
                    </svg>
                    <span class="text-muted fw-medium" style="font-size: 0.85rem; word-break: break-word; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block;">' . htmlspecialchars($nombreCorto) . '</span>
                </div>
            </a>';
    }

    // Archivos
    $stmt = $pdo->prepare("SELECT id_archivo, nombre_archivo, nombre_arch_carpe, descarga_archivo, rela_carpeta FROM archivo_carpeta WHERE rela_carpeta = :carpeta AND visible = 'si'");
    $stmt->bindParam(':carpeta', $idcarpetaPadre, PDO::PARAM_INT);
    $stmt->execute();
    $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($archivos as $a) {
        $ext = pathinfo($a['nombre_arch_carpe'], PATHINFO_EXTENSION);
        $nombreCorto = mb_substr($a['nombre_archivo'], 0, 16);

        $ruta_completa = $a['descarga_archivo']; // Asumiendo que $a['descarga_archivo'] contiene la ruta completa.
// Obtener la parte ../../uploads/admin-archivo/ de la ruta completa
        $nombreArchi = $a['nombre_arch_carpe'];

        $relaC = $a['rela_carpeta'];


        $parte_deseada = '../../uploads/admin-archivo/';

        // Concatenar $nombreArchi al final de la parte deseada
        $ruta_completa_con_nombre = $parte_deseada . $nombreArchi;


        if (mb_strlen($a['nombre_archivo']) > 16) {
            $nombreCorto .= "...";
        }
        $nombreCompleto = htmlspecialchars($a['nombre_archivo']);



$carpetaRaiza= $pdo->prepare("WITH RECURSIVE FolderHierarchy AS (
    SELECT
        id_subcarpeta AS current_folder_id,
        carpeta_padre AS parent_folder_id
    FROM
        subcarpeta
    WHERE
        id_subcarpeta = $relaC -- Comienza desde la subcarpeta con ID 2

    UNION ALL

    SELECT
        s.id_subcarpeta AS current_folder_id,
        s.carpeta_padre AS parent_folder_id
    FROM
        subcarpeta s
    JOIN
        FolderHierarchy fh ON s.id_subcarpeta = fh.parent_folder_id
)
SELECT
    fh.current_folder_id AS raiz_carpeta_id
FROM
    FolderHierarchy fh
WHERE
    fh.parent_folder_id IS NULL; -- La condición para encontrar la raíz");



 $carpetaRaiza->execute();

            $idcarpetarais = $carpetaRaiza->fetchColumn();




    $stmtz = $pdo->prepare("SELECT COUNT(*) FROM colaborador_carpetas WHERE id_carpeta = :carpeta_id AND id_colaborador = :usuario_id");
            $stmtz->bindParam(':carpeta_id', $idcarpetarais, PDO::PARAM_INT);
            $stmtz->bindParam(':usuario_id', $id_usuariox, PDO::PARAM_INT);
            $stmtz->execute();

            $countx = $stmtz->fetchColumn();




        echo '
            <a href="' . $ruta_completa_con_nombre . '"  id="' . $countx . '"
                download
                class="card text-center border-0 shadow-sm rounded p-0 cursor-pointer archivo-item"
                data-id="' . $a['id_archivo'] . '" 
                data-tipo="archivo"
                data-nombre="' . $nombreCompleto . '"
                style="width: 7rem; height: 6.6rem; text-decoration: none;"
                data-bs-toggle="tooltip" data-bs-placement="top" title="' . $nombreCompleto . ' (.' . $ext . ')">
                <div class="card-body d-flex flex-column justify-content-start align-items-center p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#6c757d" class="bi bi-files" viewBox="0 0 16 16">
                        <path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2m0 13V4a2 2 0 0 0-2-2H5a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                    </svg>
                    <span class="text-muted fw-medium" style="font-size: 0.85rem; word-break: break-word; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block;">' . htmlspecialchars($nombreCorto) . ' (.' . $ext . ')</span>
                </div>
            </a>';
    }

    if (count($subcarpetas) === 0 && count($archivos) === 0) {
        echo 'No hay carpetas ni archivos cargados';
    }
} else {

    $carpetaConsul = "SELECT c.id_carpeta, c.nombre_carpeta, c.rela_grupo, c.rela_usuario, c.grupal, c.tipo_relacion, c.visible, cc.nombre_colaborador,cc.apellido_colaborador FROM carpeta c LEFT JOIN colaborador_carpetas cc ON c.id_carpeta = cc.id_carpeta WHERE c.tipo_relacion = 2 AND (c.rela_usuario = $id_usuariox OR cc.id_colaborador = $id_usuariox) AND c.visible = 'si'";
    $stmt = $pdo->prepare($carpetaConsul);
    $stmt->execute();
    $cursosDatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($cursosDatos) {
        foreach ($cursosDatos as $c) {
            $idcarpeta = $c['id_carpeta'];
            $nombreCorto = mb_substr($c['nombre_carpeta'], 0, 16);
            if (mb_strlen($c['nombre_carpeta']) > 16) {
                $nombreCorto .= "...";
            }
            $nombrecarpeta = $c['nombre_carpeta'];




            $stmtc = $pdo->prepare("SELECT COUNT(*) FROM colaborador_carpetas WHERE id_carpeta = :carpeta_id AND id_colaborador = :usuario_id");
            $stmtc->bindParam(':carpeta_id', $idcarpeta, PDO::PARAM_INT);
            $stmtc->bindParam(':usuario_id', $id_usuariox, PDO::PARAM_INT);
            $stmtc->execute();

            $count = $stmtc->fetchColumn();


            $creadorConsul = "SELECT e.nombre, e.apellido FROM carpeta c INNER JOIN empleados e ON c.rela_usuario = e.id_empleado WHERE c.id_carpeta = :carpeta_id";
            $stmtf = $pdo->prepare($creadorConsul);
            $stmtf->bindParam(':carpeta_id', $idcarpeta, PDO::PARAM_INT);
            $stmtf->execute();
            $creadorDatos = $stmtf->fetch(PDO::FETCH_ASSOC); // Usamos fetch en lugar de fetchAll porque esperamos un solo creador



            $colaboradoresConsul = "SELECT `nombre_colaborador`, `apellido_colaborador`, `id_carpeta` FROM `colaborador_carpetas` WHERE `id_carpeta` = $idcarpeta AND id_colaborador != $id_usuariox";
            $stmtz = $pdo->prepare($colaboradoresConsul);
            $stmtz->execute();
            $colaboradorDatos = $stmtz->fetchAll(PDO::FETCH_ASSOC);

            $tooltipTitle = $nombrecarpeta;


  // Agregamos la información del creador al tooltip
  if ($creadorDatos) {
    $tooltipTitle .= " (Creado por: " . htmlspecialchars($creadorDatos['nombre'] . " " . $creadorDatos['apellido']) . ")";
}


            if ($colaboradorDatos) {
                $tooltipTitle .= " (Colaboradores: ";
                $colaboradoresArray = [];
                foreach ($colaboradorDatos as $z) {
                    $colaboradoresArray[] = $z['nombre_colaborador'] . " " . $z['apellido_colaborador'];
                }
                $tooltipTitle .= implode(", ", $colaboradoresArray) . ")";
            }

            echo '
                <a href="administradorArchivos.php?p=' . $idcarpeta . '"
                    class="card file-card text-center border-0 shadow-sm rounded p-0 cursor-pointer carpeta-item" id="' . $count . '"
                    type="button" data-id="' . $idcarpeta . '" data-tipo="carpeta"
                    data-nombre="' . htmlspecialchars($nombrecarpeta) . '" data-bs-toggle="tooltip"
                    data-bs-offset="0,8" data-bs-placement="top" data-bs-custom-class="tooltip-dark"
                    title="' . htmlspecialchars($tooltipTitle) . '" style="width: 7rem; height: 6.6rem; text-decoration: none;">
                    <div class="card-body d-flex flex-column justify-content-start align-items-center p-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#334155"
                            class="bi bi-folder-fill text-warning" viewBox="0 0 16 16">
                            <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"></path>
                        </svg>
                        <span class="file-name text-capitalize text-muted fw-medium"
                            style="font-size: 0.85rem; word-break: break-word; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block;">' . $nombreCorto . '</span>
                    </div>
                </a>';


            echo ' <input type="hidden" style="height:1px;visibility:hidden;" class="es_colaborador_input form-control" value="' . $count . '"
                 placeholder="..." />';



        }
    } else {
        echo 'No hay carpetas cargadas';
    }





}
?>