<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php $evitarValidacionUsuario = true; ?>
<?php include '../common/header.php'; ?>

<main>
    <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
            <div class="col-lg-5 col-md-8 py-8 py-xl-0">
                <!-- Card -->
                <div class="card shadow">
                    <!-- Card body -->
                    <div class="card-body p-6 d-flex flex-column gap-4">
                        <div>

                        </div>
                        <!-- Form -->
                        <form id="formEntidad" class="needs-validation" novalidate="">
                            <!-- Username -->
                            <div class="mb-3">
                                <h3 for="signUpName" class="form-label">La Aventura comienza aquí 🚀</h3>
                                <p class="mb-4">¡Haga que la administración de su institución sea fácil y divertida!</p>

                                <input type="text" id="nombreEntidad" class="form-control" name="nombreEntidad"
                                    placeholder="Nombre de la entidad" required>
                                <div class="invalid-feedback">Por favor ingrese el nombre de la entidad.</div>

                                <div class="mb-3"></div>

                                <input type="text" id="nombre" class="form-control" name="nombre" placeholder="Nombre"
                                    required>
                                <div class="invalid-feedback">Por favor ingrese su nombre.</div>

                                <div class="mb-3"></div>

                                <input type="text" id="apellido" class="form-control" name="apellido"
                                    placeholder="Apellido" required>
                                <div class="invalid-feedback">Por favor ingrese su apellido.</div>

                                <div class="mb-3"></div>

                                <input type="text" id="usuario" class="form-control" name="usuario"
                                    placeholder="Usuario" required>
                                <div class="invalid-feedback">Por favor ingrese su usuario.</div>

                                <div class="mb-3"></div>

                                <input type="password" id="contrasena" class="form-control" name="contrasena"
                                    placeholder="Contraseña" required>
                                <div class="invalid-feedback">Por favor ingrese su contraseña.</div>

                            </div>
                            <div>
                                <!-- Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Crear institución</button>
                                </div>
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
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center active"
                        data-bs-theme-value="light" aria-pressed="true">
                        <i class="bi theme-icon bi-sun-fill"></i>
                        <span class="ms-2">Light</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"
                        aria-pressed="false">
                        <i class="bi theme-icon bi-moon-stars-fill"></i>
                        <span class="ms-2">Dark</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto"
                        aria-pressed="false">
                        <i class="bi theme-icon bi-circle-half"></i>
                        <span class="ms-2">Auto</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</main>

<?php include '../common/scripts.php'; ?>

<script>
    document.getElementById('formEntidad').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../../backend/controller/activacion/crear_entidad.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Entidad creada!',
                        text: data.message,
                        confirmButtonText: 'Ingresar'
                    }).then(() => {
                        window.location.href = '../login/login.php';
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
            });
    });
</script>
<?php include '../common/footer.php'; ?>