<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<!-- Agregar el CSS personalizado para el estilo del index -->
<link rel="stylesheet" href="../common/css/index-custom.css">
<link rel="stylesheet" href="./css/capacitacion/styles.css">

<?php
$zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
$fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);

// Obtener el nombre del mes (en inglés por defecto)
$nombreMesIngles = $fechaHoraActual->format('F');

// Si necesitas el nombre del mes en español, puedes hacer lo siguiente:
// 1. Obtener el número del mes (sin ceros iniciales)
$numeroMes = $fechaHoraActual->format('n');

// 2. Usar un array para mapear el número al nombre en español
$nombresMesesEspañol = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

$nombreMesEspañol = $nombresMesesEspañol[$numeroMes];
?>

<div class="db-content principal">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Capacitaciones internas</h1>
        </div>

        <!-- Contenedor de cards con el nuevo estilo -->
        <div class="contenedor-cards mt-4">
            <a href="misCapacitaciones.php?mi_variable2=2" class="text-decoration-none">
                <div class="card card-acceso card-border-primary rounded-4 position-relative">
                    <div class="card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                        <div>
                            <svg width="50" height="50" viewBox="0 0 54 50" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M23.6272 24.378L1.41922 14.0782C-0.473072 13.1777 -0.473072 11.7898 1.41922 10.9729L23.6272 0.675333C25.5195 -0.225111 28.5638 -0.225111 30.3728 0.675333L52.5808 10.9729C54.4731 11.8711 54.4731 13.2612 52.5808 14.0782L30.3728 24.3757C28.4805 25.1927 25.4362 25.1927 23.6272 24.3757V24.378ZM23.6272 36.7179L1.41922 26.4203C-0.473072 25.5199 -0.473072 24.132 1.41922 23.3151L8.98612 19.799L23.6272 26.5828C25.5195 27.481 28.5638 27.481 30.3728 26.5828L45.0139 19.799L52.5808 23.3151C54.4731 24.2132 54.4731 25.6034 52.5808 26.4203L30.3728 36.7179C28.4805 37.6161 25.4362 37.6161 23.6272 36.7179ZM23.6272 36.7179L1.41922 26.4226C-0.473072 25.5222 -0.473072 24.132 1.41922 23.3151L8.98612 19.8013L23.6272 26.5828C25.5195 27.481 28.5638 27.481 30.3728 26.5828L45.0139 19.8013L52.5808 23.3151C54.4731 24.2132 54.4731 25.6057 52.5808 26.4226L30.3728 36.7202C28.4805 37.6183 25.4362 37.6183 23.6272 36.7202M23.6272 49.3873L1.41922 39.0897C-0.473072 38.1916 -0.473072 36.8014 1.41922 35.9845L9.15038 32.3895L23.5462 39.092C25.4362 39.9902 28.4805 39.9902 30.2896 39.092L44.6854 32.3895L52.4165 35.9845C54.3088 36.8872 54.3088 38.2728 52.4165 39.0897L30.2086 49.3873C28.4805 50.2042 25.4385 50.2042 23.6272 49.3873Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <div>
                            <h6 class="mb-0">Mis Capacitaciones</h6>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="db-content miCapacitacion" style="display:none;">
    <div class="container mb-4 mx-auto">
        <a href="misCapacitaciones.php" class="link-primary mb-3">
            <i class="bi bi-caret-left-fill fs-2 "></i>
        </a>

        <div class="row my-2 mr-2 mb-1 text-center">
            <label for="search-input" class="form-label">
                <h3>Capacitaciones de <?php echo $nombreMesEspañol; ?></h3>
            </label>
        </div>

        <input type="text" style="height:1px; visibility:hidden;" id="entidades"
            value="<?php echo $usuarioActual->id; ?>">

        <div class="row d-flex justify-content-center" id="empleadoCapacitacion">
        </div>
        <br>

        <div class="row my-2 mr-2 mb-1 text-center">
            <label for="search-input" class="form-label">
                <h3>Capacitaciones Finalizadas del mes</h3>
            </label>
        </div>

        <div class="row d-flex justify-content-center" id="finalizadaCapacitacion">
        </div>
    </div>
</div>

<script src="../../dist/assets/libs/jquery/jquery-3.7.0.min.js"></script>

<!-- Select2 -->
<link href="../../dist/assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="../../dist/assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="../../dist/assets/libs/select2/select2.min.js"></script>

<?php include '../common/scripts.php'; ?>
<script src="./js/capacitacion/miscapacitaciones.js"></script>
<?php include '../common/footer.php'; ?>