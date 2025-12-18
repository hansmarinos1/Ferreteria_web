<?php
// Evitar iniciar sesión doble si ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Ferretería Comas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        /* ESTILOS GLOBALES */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9; /* Color de fondo por defecto si la imagen falla */
            /* Imagen de fondo de herramientas */
            background-image: url('https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* CAPA OSCURA SOBRE EL FONDO */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.65); /* Oscuridad al 65% */
            z-index: -1;
        }

        /* BARRA DE NAVEGACIÓN */
        .navbar-custom {
            background: linear-gradient(90deg, #1a1a1a 0%, #333333 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffc107 !important; /* Amarillo ferretero */
        }
        .nav-link {
            font-weight: 500;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #ffc107 !important;
            transform: translateY(-2px);
        }
        .nav-link.active {
            color: #fff !important;
            border-bottom: 2px solid #ffc107;
        }

        /* CONTENEDOR PRINCIPAL (EFECTO VIDRIO) */
        .main-container {
            background-color: rgba(255, 255, 255, 0.96);
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            min-height: 80vh; /* Altura mínima para que se vea bien */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-tools"></i> FERRETERÍA COMAS
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Inicio</a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i> Gestión
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="ventas.php"><i class="bi bi-cart-check"></i> Ventas</a></li>
                        <li><a class="dropdown-item" href="productos.php"><i class="bi bi-tools"></i> Productos</a></li>
                        <li><a class="dropdown-item" href="clientes.php"><i class="bi bi-people"></i> Clientes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="Reportes.php"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="reclamos.php"><i class="bi bi-journal-x"></i> Reclamos</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="sugerencias.php"><i class="bi bi-lightbulb"></i> Sugerencias</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-info-circle"></i> Info
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="ubicacion.php"><i class="bi bi-geo-alt"></i> Ubicación</a></li>
                        <li><a class="dropdown-item" href="videos.php"><i class="bi bi-youtube"></i> Tutoriales</a></li>
                    </ul>
                </li>

                <li class="nav-item ms-lg-3">
                    <a class="btn btn-outline-warning btn-sm fw-bold px-3" href="../logout.php">
                        <i class="bi bi-power"></i> Salir
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<div class="container main-container">
    