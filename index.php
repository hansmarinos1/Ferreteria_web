<?php
session_start();

/* ==========================================
   1. SEGURIDAD Y CONFIGURACIÓN
========================================== */
header('X-Frame-Options: DENY'); // Protección contra iframes
header('X-Content-Type-Options: nosniff'); // Protección MIME

// Si ya hay sesión, ir directo al dashboard
if (isset($_SESSION['usuario'])) {
    header("Location: views/dashboard.php");
    exit;
}

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso | Ferretería Comas</title>
    <link rel="icon" href="public/img/favicon.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Fondo con imagen de ferretería y superposición oscura */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1581783342308-f792ca43d5b1?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        .card-login {
            background: rgba(255, 255, 255, 0.95); /* Blanco semitransparente */
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        /* Línea superior decorativa (Amarillo Ferretería) */
        .card-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #ffc107; 
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 2.5rem;
            color: #ffc107; /* Icono amarillo */
            background: #212529; /* Fondo negro */
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            display: inline-block;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .login-header h3 {
            font-weight: 700;
            color: #212529;
            margin-bottom: 5px;
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
            padding-left: 15px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.25);
            border-color: #ffc107;
        }

        .btn-login {
            height: 50px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            border-radius: 8px;
            background-color: #212529;
            border: none;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            background-color: #000;
            transform: translateY(-2px);
        }

        .alert-custom {
            font-size: 0.9rem;
            border: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container px-4">
    <div class="card-login mx-auto animate-fade-in">
        
        <div class="login-header">
            <i class="bi bi-hammer"></i>
            <h3>Bienvenido</h3>
            <p class="text-muted small">Ingrese su usuario y contraseña para acceder</p>
        </div>

        <div id="alert-container">
            <?php if(isset($_SESSION['error_login'])): ?>
                <div class="alert alert-danger alert-custom d-flex align-items-center mb-3 p-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($_SESSION['error_login']) ?></div>
                </div>
                <?php unset($_SESSION['error_login']); ?>
            <?php endif; ?>

            <?php if(isset($_GET['m']) && $_GET['m'] == 1): ?>
                <div class="alert alert-success alert-custom d-flex align-items-center mb-3 p-2" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>Sesión cerrada correctamente.</div>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="controllers/loginController.php" class="needs-validation" novalidate autocomplete="off" id="loginForm">
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">USUARIO</label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="usuario" name="usuario" 
                           placeholder="Ej: admin" required 
                           value="<?= isset($_GET['u']) ? htmlspecialchars($_GET['u']) : '' ?>">
                    <div class="invalid-feedback">Ingrese su usuario.</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">CONTRASEÑA</label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                           placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary border border-start-0" type="button" id="togglePass">
                        <i class="bi bi-eye"></i>
                    </button>
                    <div class="invalid-feedback">Ingrese su contraseña.</div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-primary btn-login text-white" type="submit" id="btnSubmit">
                    <span id="btnText">INICIAR SESIÓN</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>

        </form>

        <div class="text-center mt-4 text-muted small">
            &copy; <?= date('Y') ?> <strong>Ferretería Comas</strong><br>Sistema de Gestión 
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Mostrar/Ocultar contraseña (Mejorado con icono)
    const togglePassBtn = document.getElementById('togglePass');
    const passInput = document.getElementById('password');
    
    togglePassBtn.addEventListener('click', function () {
        const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passInput.setAttribute('type', type);
        
        // Cambiar icono
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // 2. Validación y Efecto de Carga (Loading)
    const form = document.getElementById('loginForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            // Si el formulario es válido, mostrar spinner y deshabilitar botón
            // (Nota: No prevenimos el envío aquí, dejamos que el form se envíe al PHP)
            btnSubmit.classList.add('disabled');
            btnText.textContent = 'Verificando...';
            btnSpinner.classList.remove('d-none');
        }
        form.classList.add('was-validated');
    }, false);
</script>

</body>
</html>