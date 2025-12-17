<?php $evitarValidacionUsuario = true; ?>
<?php include '../common/header.php'; ?>

<?php
$error = "";

try {
    // Verificar si ya hay una licencia activa
    $stmt = $pdo->query("SELECT id_licencia FROM licencias WHERE activa = 1 LIMIT 1");
    if ($stmt->fetch()) {
        header("Location: creacionEntidad.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = trim($_POST['signUpName'] ?? '');

        if ($codigo === '') {
            $error = "Por favor ingrese un código de licencia.";
        } else {
            $stmt = $pdo->prepare("SELECT id_licencia FROM licencias WHERE codigo_licencia = :codigo AND activa = 1");
            $stmt->execute([':codigo' => $codigo]);
            $licencia = $stmt->fetch();

            if ($licencia) {
                $_SESSION['usuario']['id_licencia_activada'] = $licencia['id_licencia'];
                header("Location: creacionEntidad.php");
                exit;
            } else {
                $error = "Código inválido o licencia no activa.";
            }
        }
    }
} catch (PDOException $e) {
    $error = "Error al conectar con la base de datos.";
}
?>

<main>
  <section class="container d-flex flex-column vh-100">
    <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
      <div class="col-lg-5 col-md-8 py-8 py-xl-0">
        <div class="card shadow">
          <div class="card-body p-6 d-flex flex-column gap-4">
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger mb-0"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form id="formLicencia" class="needs-validation" novalidate>
              <div class="mb-3">
                <h3 for="signUpName" class="form-label">Código de Activación</h3>
                <input type="text" id="signUpName" class="form-control" name="signUpName"
                  placeholder="2A-41-5G-13-5G-DD-9K-3E-5G-10" required>
                <div class="invalid-feedback">Por favor ingrese su código de licencia.</div>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Activar</button>
              </div>
              <hr class="my-4">
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="position-absolute bottom-0 m-4">
    <div class="dropdown">
      <button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button"
        aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
        <i class="bi theme-icon-active"><i class="bi theme-icon bi-sun-fill"></i></i>
        <span class="visually-hidden bs-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
        <li><button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="light"><i class="bi theme-icon bi-sun-fill"></i><span class="ms-2">Light</span></button></li>
        <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"><i class="bi theme-icon bi-moon-stars-fill"></i><span class="ms-2">Dark</span></button></li>
        <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto"><i class="bi theme-icon bi-circle-half"></i><span class="ms-2">Auto</span></button></li>
      </ul>
    </div>
  </div>
</main>

<?php include '../common/scripts.php'; ?>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script>
  document.getElementById('formLicencia').addEventListener('submit', function (e) {
    e.preventDefault();
    const codigo = document.getElementById('signUpName').value.trim();

    if (!codigo) {
      Swal.fire('Error', 'Por favor ingrese el código de licencia.', 'error');
      return;
    }

    fetch('../../backend/controller/activacion/activar_licencia.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'signUpName=' + encodeURIComponent(codigo)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        Swal.fire('Licencia activada', data.message, 'success').then(() => {
          window.location.href = 'creacionEntidad.php';
        });
      } else {
        Swal.fire('Error', data.message, 'error');
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
    });
  });
</script>
<?php include '../common/footer.php'; ?>
