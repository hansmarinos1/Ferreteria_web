<?php
session_start();

/* ==========================
   SEGURIDAD DE ACCESO
========================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'layout/header.php';
?>

<div class="container py-5">

    <h2 class="text-center fw-bold mb-3">
        🎥 Videos de Productos y Servicios
    </h2>

    <p class="text-center text-muted mb-5">
        Conoce el uso correcto de nuestras herramientas, productos y los servicios que ofrece la ferretería.
    </p>

    <div class="row g-4">

        <!-- VIDEO 1 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body d-flex flex-column">

                    <h5 class="card-title text-center fw-semibold mb-3">
                        Herramientas y Equipos de Ferretería
                    </h5>

                    <div class="ratio ratio-16x9 rounded overflow-hidden flex-grow-1">
                        <iframe
                            src="https://www.youtube.com/embed/48q9AKMnsms?rel=0"
                            title="Video sobre herramientas de ferretería"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>

                </div>
            </div>
        </div>

        <!-- VIDEO 2 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body d-flex flex-column">

                    <h5 class="card-title text-center fw-semibold mb-3">
                        Uso y Servicios de Productos
                    </h5>

                    <div class="ratio ratio-16x9 rounded overflow-hidden flex-grow-1">
                        <iframe
                            src="https://www.youtube.com/embed/Bf5MGvTk4l4?rel=0"
                            title="Video sobre servicios y uso de productos"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
