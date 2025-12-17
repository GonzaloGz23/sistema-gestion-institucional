<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>
<style>
    .bi-star-fill {
        color: gold;
    }

    .cursor-move {
        cursor: move;
    }

    .responsive-iframe-container {
        position: relative;
        width: 100%;
        padding-bottom: 75%;
        /* Ajusta la proporción aquí (4:3 sería 75%, 16:9 sería 56.25%) */
        height: 0;
        overflow: hidden;
    }

    .responsive-iframe-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
</style>

<?php $idUsuario = $usuarioActual->id; ?>

<div class="db-content" data-user="<?php echo $idUsuario; ?>">
    <div class="container mb-4">
        <div class="row my-2">
            <h1 class="h2 mb-0">Mis resultados <span class="text-gray-500">
                </span></h1>
        </div>

        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="responsive-iframe-container">
                        <iframe
                            src="https://lookerstudio.google.com/embed/reporting/4b682e90-7eeb-4976-809d-e2692d5f0321/page/p_4cxz58g3sd"
                            frameborder="0"
                            allowfullscreen
                            sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox">
                        </iframe>
                    </div>

                </div>
            </section>
        </div>
        <div class="row my-2">
            <h1 class="h2 mb-0">Informe Semanal/Resultado <span class="text-gray-500">
                </span></h1>
        </div>

        <div class="row gy-4">
            <section class="py-6">
                <div class="container">
                    <div class="responsive-iframe-container">
                        <iframe width="600" height="425" src="https://lookerstudio.google.com/embed/reporting/7f09aafb-babd-4a41-bee1-2e39839dadcd/page/p_ku27huisvd" frameborder="0" style="border:0" allowfullscreen sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>
                    </div>

                </div>
            </section>
        </div>
    </div>
    <!-- Modal para ver el detalle de la solicitud -->


</div>

<?php include '../common/scripts.php'; ?>
<script src="./js/formularios/formulariouser.js"></script>
<?php include '../common/footer.php'; ?>