<?php
$base = (basename($_SERVER['PHP_SELF']) === 'index.php') ? './' : '../';
$archivoActual = basename($_SERVER['PHP_SELF']);

// Obtener módulos asignados al rol actual
$modulos = [];
if (!empty($usuarioActual->id_rol)) {
  $stmt = $pdo->prepare("
        SELECT m.nombre, m.ruta, m.icono_svg, m.perfil
    FROM modulos m
    INNER JOIN roles_modulos rm ON m.id_modulo = rm.id_modulo
    WHERE rm.id_rol = :idRol AND m.activo = 'Activo' and m.id_modulo_fk IS NULL
    ORDER BY m.perfil ASC, m.orden ASC
    ");
  $stmt->execute([':idRol' => $usuarioActual->id_rol]);
  $modulos = $stmt->fetchAll();
}

$modulosAdmin = array_filter($modulos, fn($m) => $m['perfil'] === 'admin');
$modulosUser = array_filter($modulos, fn($m) => $m['perfil'] === 'user');

// Función para ajustar tamaño de icono SVG
function ajustarTamanioSvg($svg, $size = 20)
{
  // Elimina atributos de tamaño existentes
  $svg = preg_replace('/(width|height)="[^"]*"/', '', $svg);
  // Agrega nuevos atributos al tag <svg>
  return preg_replace('/<svg/', "<svg width=\"$size\" height=\"$size\"", $svg);
}
$modulosUnificados = array_merge($modulosAdmin, $modulosUser);
?>

<style>
  .nav-link svg {
    color: #64748B!important;
}
</style>

<div class="position-relative d-none d-md-block">
  <nav class="navbar navbar-expand-lg sidenav sidenav-navbar">
    <div class="collapse navbar-collapse" id="sidenavNavbar">
      <ul class="navbar-nav flex-column mt-4">

        <!-- Inicio -->
        <li class="nav-item">
          <a class="icon-image nav-link d-flex align-items-center gap-2 <?= ($archivoActual === 'index.php') ? 'active' : '' ?>"
            href="<?= $base ?>index.php">
            <svg width="18" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M0 16V7C0 6.68333 0.0709998 6.38333 0.213 6.1C0.355 5.81667 0.550667 5.58333 0.8 5.4L6.8 0.9C7.15 0.633333 7.55 0.5 8 0.5C8.45 0.5 8.85 0.633333 9.2 0.9L15.2 5.4C15.45 5.58333 15.646 5.81667 15.788 6.1C15.93 6.38333 16.0007 6.68333 16 7V16C16 16.55 15.804 17.021 15.412 17.413C15.02 17.805 14.5493 18.0007 14 18H11C10.7167 18 10.4793 17.904 10.288 17.712C10.0967 17.52 10.0007 17.2827 10 17V12C10 11.7167 9.904 11.4793 9.712 11.288C9.52 11.0967 9.28267 11.0007 9 11H7C6.71667 11 6.47933 11.096 6.288 11.288C6.09667 11.48 6.00067 11.7173 6 12V17C6 17.2833 5.904 17.521 5.712 17.713C5.52 17.905 5.28267 18.0007 5 18H2C1.45 18 0.979333 17.8043 0.588 17.413C0.196666 17.0217 0.000666667 16.5507 0 16Z"
                fill="currentColor" />
            </svg>

            <span>Inicio</span>
          </a>
        </li>

        <!-- Lista unificada (sin título pero con badge Admin) -->
        <?php foreach ($modulosUnificados as $mod): ?>
          <?php
          $ruta = ($mod['perfil'] === 'admin')
            ? $base . 'admin/' . $mod['ruta']
            : $base . 'user/' . $mod['ruta'];

          $icono = $mod['icono_svg']
            ? ajustarTamanioSvg($mod['icono_svg'], 18)
            : ($mod['perfil'] === 'admin'
              ? '<i class="fe fe-grid nav-icon"></i>'
              : '<i class="fe fe-tablet nav-icon"></i>');
          ?>
          <li class="nav-item">
            <a class="icon-image nav-link d-flex align-items-center gap-2 <?= ($archivoActual === $mod['ruta']) ? 'active' : '' ?>"
              href="<?= $ruta ?>">
              <?= $icono ?>
              <span class="flex-grow-1"><?= htmlspecialchars($mod['nombre']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>

        <!-- Cierre de Sesión -->
        <li class="nav-item">
          <a class="icon-image nav-link d-flex align-items-center gap-2" href="<?= ROOT_PATH ?>backend/controller/auth/logout.php">
            <svg width="18" height="18" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M0.125 22C0.125 16.1984 2.42968 10.6344 6.53204 6.53204C10.6344 2.42968 16.1984 0.125 22 0.125C27.8016 0.125 33.3656 2.42968 37.468 6.53204C41.5703 10.6344 43.875 16.1984 43.875 22C43.875 27.8016 41.5703 33.3656 37.468 37.468C33.3656 41.5703 27.8016 43.875 22 43.875C16.1984 43.875 10.6344 41.5703 6.53204 37.468C2.42968 33.3656 0.125 27.8016 0.125 22ZM22 7.625C22.4973 7.625 22.9742 7.82254 23.3258 8.17417C23.6775 8.52581 23.875 9.00272 23.875 9.5V22C23.875 22.4973 23.6775 22.9742 23.3258 23.3258C22.9742 23.6775 22.4973 23.875 22 23.875C21.5027 23.875 21.0258 23.6775 20.6742 23.3258C20.3225 22.9742 20.125 22.4973 20.125 22V9.5C20.125 9.00272 20.3225 8.52581 20.6742 8.17417C21.0258 7.82254 21.5027 7.625 22 7.625ZM17 12.205C17 11.7475 16.525 11.45 16.13 11.675C13.8518 12.9705 12.0662 14.9832 11.0514 17.3995C10.0366 19.8158 9.84962 22.5 10.5196 25.0336C11.1896 27.5673 12.679 29.8081 14.7555 31.4069C16.8321 33.0058 19.3792 33.8727 22 33.8727C24.6208 33.8727 27.1679 33.0058 29.2445 31.4069C31.321 29.8081 32.8104 27.5673 33.4804 25.0336C34.1504 22.5 33.9634 19.8158 32.9486 17.3995C31.9338 14.9832 30.1482 12.9705 27.87 11.675C27.4725 11.45 27 11.75 27 12.205V15.29C27 15.4825 27.09 15.665 27.2375 15.7875C28.511 16.8613 29.4237 18.3005 29.8519 19.9103C30.2801 21.52 30.2033 23.2225 29.6317 24.7871C29.0601 26.3518 28.0214 27.7029 26.6563 28.6575C25.2913 29.6121 23.6658 30.1241 22 30.1241C20.3342 30.1241 18.7087 29.6121 17.3437 28.6575C15.9786 27.7029 14.9399 26.3518 14.3683 24.7871C13.7967 23.2225 13.7199 21.52 14.1481 19.9103C14.5763 18.3005 15.489 16.8613 16.7625 15.7875C16.8361 15.727 16.8956 15.6511 16.9366 15.5652C16.9776 15.4792 16.9993 15.3853 17 15.29V12.205Z" fill="currentColor"/>
</svg>
            <span>Salir</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>
</div>