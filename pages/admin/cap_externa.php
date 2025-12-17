<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>
<link rel="stylesheet" href="../common/css/index-custom.css">

<div class="db-content">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Gestión de Capacitación Externa</h1>
        </div>
        <div class="contenedor-cards mt-4">
          
            <a href="./revisionCapacitaciones.php"
                class="card card-acceso card-border-primary rounded-4 text-decoration-none position-relative">
                <div class="card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                    <div>
                        <svg width="50" height="50" viewBox="0 0 45 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.54232 0.417969C2.82402 0.417969 2.13515 0.70331 1.62724 1.21122C1.11933 1.71913 0.833984 2.40801 0.833984 3.1263V46.4596C0.833984 48.6145 1.69001 50.6811 3.21374 52.2049C4.73747 53.7286 6.8041 54.5846 8.95898 54.5846H36.0423C38.1972 54.5846 40.2638 53.7286 41.7876 52.2049C43.3113 50.6811 44.1673 48.6145 44.1673 46.4596V16.668C44.1672 15.9497 43.8817 15.261 43.3738 14.7532L29.8321 1.21151C29.3243 0.703564 28.6356 0.418122 27.9173 0.417969H3.54232ZM27.9173 6.95589L37.6294 16.668H27.9173V6.95589ZM32.5404 32.1244C33.0338 31.6136 33.3068 30.9295 33.3006 30.2194C33.2944 29.5093 33.0096 28.83 32.5075 28.3278C32.0053 27.8257 31.326 27.5409 30.6159 27.5347C29.9058 27.5285 29.2217 27.8015 28.7109 28.2948L19.7923 37.2134L16.2904 33.7115C15.7796 33.2182 15.0955 32.9452 14.3854 32.9514C13.6753 32.9575 12.996 33.2424 12.4939 33.7445C11.9917 34.2467 11.7069 34.9259 11.7007 35.6361C11.6945 36.3462 11.9675 37.0303 12.4609 37.5411L17.8775 42.9578C18.3854 43.4655 19.0742 43.7507 19.7923 43.7507C20.5105 43.7507 21.1992 43.4655 21.7071 42.9578L32.5404 32.1244Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <h6 class="mb-0">Capacitación</h6>
                    </div>
                </div>
            </a>
              <a href="./asistencia_capacitacones.php"
                class="card card-acceso card-border-primary rounded-4 text-decoration-none position-relative">
                <div class="card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                    <div>
                        <svg width="50" height="50" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M19.0833 43.7562L24.7979 38.0417H10.9583V32.625H29.9167V32.95L35.6583 27.2083H10.9583V21.7917H38.0417V24.825L41.4 21.4937C42.7 20.1938 44.4062 19.4625 46.275 19.4625C47.1688 19.4625 48.0625 19.6521 48.875 19.9771V5.54167C48.875 4.10508 48.3043 2.72733 47.2885 1.7115C46.2727 0.695683 44.8949 0.125 43.4583 0.125H5.54167C2.53542 0.125 0.125 2.53542 0.125 5.54167V43.4583C0.125 44.8949 0.695683 46.2727 1.7115 47.2885C2.72733 48.3043 4.10508 48.875 5.54167 48.875H19.0833V43.7562ZM10.9583 10.9583H38.0417V16.375H10.9583V10.9583ZM50.7708 30.8646L48.0625 33.5729L42.5104 28.0208L45.2187 25.3125C45.4972 25.0396 45.8716 24.8867 46.2615 24.8867C46.6514 24.8867 47.0257 25.0396 47.3042 25.3125L50.7708 28.7792C51.3396 29.3479 51.3396 30.2958 50.7708 30.8646ZM24.5 46.0042L40.9125 29.5917L46.4646 35.1437L30.0792 51.5833H24.5V46.0042Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <h6 class="mb-0">Asistencia</h6>
                    </div>
                </div>
            </a>
            <a href="./historial_capacitacion.php"
                class="card card-acceso card-border-primary rounded-4 text-decoration-none position-relative">
                <div class="card-body d-flex flex-column justify-content-center gap-2 text-center h-100">
                    <div>
                        <svg width="54" height="50" viewBox="0 0 54 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M23.6272 24.378L1.41922 14.0782C-0.473072 13.1777 -0.473072 11.7898 1.41922 10.9729L23.6272 0.675333C25.5195 -0.225111 28.5638 -0.225111 30.3728 0.675333L52.5808 10.9729C54.4731 11.8711 54.4731 13.2612 52.5808 14.0782L30.3728 24.3757C28.4805 25.1927 25.4362 25.1927 23.6272 24.3757V24.378ZM23.6272 36.7179L1.41922 26.4203C-0.473072 25.5199 -0.473072 24.132 1.41922 23.3151L8.98612 19.799L23.6272 26.5828C25.5195 27.481 28.5638 27.481 30.3728 26.5828L45.0139 19.799L52.5808 23.3151C54.4731 24.2132 54.4731 25.6034 52.5808 26.4203L30.3728 36.7179C28.4805 37.6161 25.4362 37.6161 23.6272 36.7179ZM23.6272 36.7179L1.41922 26.4226C-0.473072 25.5222 -0.473072 24.132 1.41922 23.3151L8.98612 19.8013L23.6272 26.5828C25.5195 27.481 28.5638 27.481 30.3728 26.5828L45.0139 19.8013L52.5808 23.3151C54.4731 24.2132 54.4731 25.6057 52.5808 26.4226L30.3728 36.7202C28.4805 37.6183 25.4362 37.6183 23.6272 36.7202M23.6272 49.3873L1.41922 39.0897C-0.473072 38.1916 -0.473072 36.8014 1.41922 35.9845L9.15038 32.3895L23.5462 39.092C25.4362 39.9902 28.4805 39.9902 30.2896 39.092L44.6854 32.3895L52.4165 35.9845C54.3088 36.8872 54.3088 38.2728 52.4165 39.0897L30.2086 49.3873C28.4805 50.2042 25.4385 50.2042 23.6272 49.3873Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <h6 class="mb-0">Historial</h6>
                    </div>
                </div>
            </a> <!--         <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                <a href="./respuestas.php" class="text-decoration-none">
                    <div class="card-hover-svg card card-body d-flex flex-column gap-4">
                        <div class="">
                            <span class="icon-shape icon-xxl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor"
                                    class="bi bi-file-text" viewBox="0 0 16 16">
                                    <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z" />
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <h3 class="mb-0">Respuestas</h3>
                        </div>
                    </div>
                </a>
            </div> -->
        </div>
    </div>
</div>




<?php include '../common/scripts.php'; ?>
<?php include '../common/footer.php'; ?>