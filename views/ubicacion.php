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

    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary"><i class="bi bi-geo-alt-fill"></i> Encuéntranos</h2>
        <p class="text-muted">Estamos ubicados estratégicamente en Comas para atenderte mejor.</p>
    </div>

    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">

                <div class="col-lg-4 bg-light p-5 d-flex flex-column justify-content-center">
                    
                    <h4 class="fw-bold mb-4 text-dark">Ferretería Comas</h4>

                    <div class="d-flex align-items-start mb-4">
                        <div class="icon-square bg-white text-danger rounded-circle p-2 shadow-sm me-3">
                            <i class="bi bi-map fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Dirección</h6>
                            <p class="text-muted mb-0 small">
                                Av. Túpac Amaru Km 11<br>
                                Comas, Lima - Perú
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="icon-square bg-white text-primary rounded-circle p-2 shadow-sm me-3">
                            <i class="bi bi-clock fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Horario de Atención</h6>
                            <p class="text-muted mb-0 small">
                                Lun - Vie: 8:00 AM - 7:00 PM<br>
                                Sábados: 9:00 AM - 2:00 PM
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="icon-square bg-white text-success rounded-circle p-2 shadow-sm me-3">
                            <i class="bi bi-telephone fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Contacto</h6>
                            <p class="text-muted mb-0 small">
                                <a href="tel:+51999999999" class="text-decoration-none text-muted">
                                    +51 999 999 999
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <a href="https://wa.me/51999999999?text=Hola,%20quisiera%20información%20de%20productos" 
                           target="_blank" class="btn btn-success rounded-pill">
                            <i class="bi bi-whatsapp"></i> Contactar por WhatsApp
                        </a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=Comas+Lima" 
                           target="_blank" class="btn btn-outline-dark rounded-pill">
                            <i class="bi bi-car-front-fill"></i> Cómo llegar (Waze/Maps)
                        </a>
                    </div>

                </div>

                <div class="col-lg-8">
                    <div class="h-100" style="min-height: 450px;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d62456.63469606834!2d-77.08778087799516!3d-11.93666756743845!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105d1cb3775e77d%3A0x6334da4659b850d5!2sComas!5e0!3m2!1ses!2spe!4v1700000000000!5m2!1ses!2spe" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>