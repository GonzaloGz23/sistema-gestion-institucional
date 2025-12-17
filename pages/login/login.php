<?php
$evitarValidacionUsuario = true;

// Incluir configuración de sesión para tener acceso a verificarUsuarioAutenticado()
require_once '../../backend/config/session_config.php';

// Verificar si el usuario ya está autenticado y redirigir
if (verificarUsuarioAutenticado()) {
    header("Location: ../index.php");
    exit;
}

// Ahora incluimos el header después de la verificación
include '../common/header.php';
?>
<main>
    <section class="container d-flex flex-column vh-100">
        <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
            <div class="col-lg-5 col-md-8 py-8 py-xl-0">
                
                <!-- ✅ Componente de instalación PWA -->
                <div id="pwa-install-component" class="alert d-none mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i id="pwa-icon" class="me-2"></i>
                        <div class="flex-grow-1">
                            <strong id="pwa-title"></strong>
                            <div id="pwa-subtitle" class="small"></div>
                        </div>
                        <button id="pwa-action-btn" class="btn btn-sm">
                        </button>
                    </div>
                </div>

                <!-- Card de Login -->
                <form id="formLogin" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" placeholder="usuario" required>
                        <div class="invalid-feedback">Ingrese su usuario.</div>
                    </div>

                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="*********" required>
                        <div class="invalid-feedback">Ingrese su contraseña.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
</main>

<?php 
$ocultarMenuMovil = true;
include '../common/scripts.php'; 
?>

<script>
// ✅ Componente de instalación PWA para móviles
class PWAInstaller {
    constructor() {
        this.component = document.getElementById('pwa-install-component');
        this.icon = document.getElementById('pwa-icon');
        this.title = document.getElementById('pwa-title');
        this.subtitle = document.getElementById('pwa-subtitle');
        this.actionBtn = document.getElementById('pwa-action-btn');
        this.deferredPrompt = null;
        
        this.init();
    }

    // Detectar si es dispositivo móvil
    isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               window.innerWidth <= 768;
    }

    // Detectar si ya está en modo PWA
    isInPWAMode() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
               (window.navigator && window.navigator.standalone) ||
               document.referrer.includes('android-app://');
    }

    // Detectar si la PWA está instalada pero se accede desde navegador
    async isPWAInstalled() {
        // Método 1: getInstalledRelatedApps (Chrome moderno)
        if ('getInstalledRelatedApps' in navigator) {
            try {
                const relatedApps = await navigator.getInstalledRelatedApps();
                if (relatedApps.length > 0) {
                    return true;
                }
            } catch (error) {
                console.log('getInstalledRelatedApps no disponible');
            }
        }

        // Método 2: Verificar si beforeinstallprompt no se dispara
        return new Promise((resolve) => {
            let promptFired = false;
            
            const promptHandler = () => {
                promptFired = true;
                resolve(false); // Si se dispara, NO está instalada
            };

            window.addEventListener('beforeinstallprompt', promptHandler, { once: true });

            // Si después de 2 segundos no se disparó, probablemente está instalada
            setTimeout(() => {
                window.removeEventListener('beforeinstallprompt', promptHandler);
                resolve(!promptFired);
            }, 2000);
        });
    }

    async init() {
        console.log('🔍 Inicializando PWA Installer...');

        // Caso 3: Si ya está en modo PWA, no mostrar nada
        if (this.isInPWAMode()) {
            console.log('📱 Ya en modo PWA - No mostrar componente');
            return;
        }

        // Solo para dispositivos móviles
        if (!this.isMobileDevice()) {
            console.log('🖥️ Dispositivo de escritorio - No mostrar componente');
            return;
        }

        console.log('📱 Dispositivo móvil detectado - Verificando estado PWA...');

        // Verificar si está instalada
        const isInstalled = await this.isPWAInstalled();

        if (isInstalled) {
            // Caso 2: PWA instalada pero en navegador
            this.showOpenAppComponent();
        } else {
            // Caso 1: PWA no instalada
            this.showInstallComponent();
        }
    }

    // Caso 1: Mostrar componente para instalar
    showInstallComponent() {
        console.log('📲 Mostrando componente de instalación');
        
        // Configurar apariencia
        this.component.className = 'alert alert-info d-block mb-3';
        this.icon.className = 'bi bi-download me-2';
        this.title.textContent = '¡Instala SIGE!';
        this.subtitle.textContent = 'Acceso rápido desde tu dispositivo';
        this.actionBtn.className = 'btn btn-sm btn-outline-primary';
        this.actionBtn.innerHTML = '<i class="bi bi-download"></i> Instalar';

        // Escuchar evento de instalación
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('📱 PWA instalable detectada');
            e.preventDefault();
            this.deferredPrompt = e;
        });

        // Manejar click en instalar
        this.actionBtn.addEventListener('click', () => this.handleInstall());

        // Detectar instalación exitosa
        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA instalada exitosamente');
            Swal.fire({
                icon: 'success',
                title: '¡App instalada!',
                text: 'SIGE está ahora disponible en tu pantalla de inicio',
                timer: 3000,
                showConfirmButton: false
            });
            
            // Cambiar a modo "Abrir App"
            setTimeout(() => this.showOpenAppComponent(), 3500);
        });
    }

    // Caso 2: Mostrar componente para abrir app
    showOpenAppComponent() {
        console.log('✅ Mostrando componente "Abrir App"');
        
        // Configurar apariencia
        this.component.className = 'alert alert-success d-block mb-3';
        this.icon.className = 'bi bi-app me-2';
        this.title.textContent = '¡Tienes SIGE instalado!';
        this.subtitle.textContent = 'Para mejor experiencia, ábrelo desde la app';
        this.actionBtn.className = 'btn btn-sm btn-success';
        this.actionBtn.innerHTML = '<i class="bi bi-box-arrow-up-right"></i> Abrir App';

        // Manejar click en abrir app
        this.actionBtn.addEventListener('click', () => this.handleOpenApp());
    }

    // Manejar instalación
    async handleInstall() {
        console.log('👆 Usuario quiere instalar PWA');

        if (this.deferredPrompt) {
            // Instalación automática disponible
            try {
                console.log('🚀 Mostrando prompt de instalación');
                this.deferredPrompt.prompt();
                
                const { outcome } = await this.deferredPrompt.userChoice;
                console.log('👆 Usuario respondió:', outcome);
                
                if (outcome === 'accepted') {
                    console.log('✅ Usuario aceptó instalar');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Instalando!',
                        text: 'La app se está instalando...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    console.log('❌ Usuario rechazó instalar');
                }
                
                this.deferredPrompt = null;
            } catch (error) {
                console.error('Error en instalación:', error);
                this.showManualInstallInstructions();
            }
        } else {
            // Mostrar instrucciones manuales
            this.showManualInstallInstructions();
        }
    }

    // Mostrar instrucciones manuales de instalación
    showManualInstallInstructions() {
        const isAndroid = /Android/i.test(navigator.userAgent);
        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

        let instructions = '';
        let title = '📱 Instalar SIGE';

        if (isAndroid) {
            title = '🤖 Instalar en Android';
            instructions = `
                <div style="text-align: left; padding: 10px;">
                    <p><strong>Para instalar SIGE:</strong></p>
                    <ol>
                        <li>Toca el menú <strong>⋮</strong> (esquina superior derecha)</li>
                        <li>Busca <strong>"Instalar aplicación"</strong> o <strong>"Añadir a inicio"</strong></li>
                        <li>Toca <strong>"Instalar"</strong></li>
                    </ol>
                    <p style="color: #28a745;"><strong>✨ ¡Tendrás acceso directo desde tu pantalla de inicio!</strong></p>
                </div>
            `;
        } else if (isIOS) {
            title = '🍎 Instalar en iOS';
            instructions = `
                <div style="text-align: left; padding: 10px;">
                    <p><strong>Para instalar SIGE:</strong></p>
                    <ol>
                        <li>Toca el botón <strong>Compartir</strong> <span style="font-size: 18px;">⬆️</span></li>
                        <li>Desliza y busca <strong>"Añadir a inicio"</strong> 🏠</li>
                        <li>Toca <strong>"Añadir"</strong></li>
                    </ol>
                    <p style="color: #007bff;"><strong>✨ ¡La app aparecerá en tu pantalla de inicio!</strong></p>
                </div>
            `;
        } else {
            instructions = `
                <div style="text-align: left; padding: 10px;">
                    <p><strong>Para instalar SIGE:</strong></p>
                    <ol>
                        <li>Busca el menú de tu navegador</li>
                        <li>Busca la opción "Instalar" o "Añadir a inicio"</li>
                        <li>Confirma la instalación</li>
                    </ol>
                </div>
            `;
        }

        Swal.fire({
            icon: 'info',
            title: title,
            html: instructions,
            confirmButtonText: '¡Entendido!',
            confirmButtonColor: '#28a745',
            width: '90%'
        });
    }

    // Manejar abrir app
    handleOpenApp() {
        console.log('👆 Usuario quiere abrir la app');
        
        Swal.fire({
            icon: 'info',
            title: '📱 Abrir SIGE App',
            html: `
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 48px; margin-bottom: 15px;">📱</div>
                    <p><strong>Para una mejor experiencia:</strong></p>
                    <ol style="text-align: left; padding-left: 30px;">
                        <li>Busca el ícono de <strong>SIGE</strong> en tu pantalla de inicio</li>
                        <li>Toca para abrir la aplicación</li>
                    </ol>
                    <p style="color: #28a745; font-weight: bold;">✨ ¡Disfruta de la experiencia completa!</p>
                </div>
            `,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#28a745',
            showCancelButton: true,
            cancelButtonText: 'Continuar aquí',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isDismissed) {
                // Usuario eligió continuar en navegador, ocultar componente temporalmente
                this.component.classList.add('d-none');
            }
        });
    }
}

// ✅ Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new PWAInstaller();
});

// ✅ Manejo de login existente
document.getElementById('formLogin').addEventListener('submit', function (e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    fetch('../../backend/controller/auth/login.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '../index.php';
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error de login:', error);
        
        // ✅ Manejo mejorado de errores offline
        if (!navigator.onLine) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin conexión',
                text: 'No hay conexión a internet. El login requiere conexión para validar tus credenciales.',
                footer: 'Conéctate a internet e inténtalo de nuevo'
            });
        } else {
            Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
        }
    });
});
</script>

<?php include '../common/footer.php'; ?>