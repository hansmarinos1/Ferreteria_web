</div> <style>
    .footer-custom {
        background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%);
        color: #adb5bd;
        font-size: 0.9rem;
        margin-top: 50px; /* Separación del contenido */
    }
    .footer-custom h5 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 1.2rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .footer-link {
        color: #adb5bd;
        text-decoration: none;
        transition: all 0.3s ease;
        display: block;
        margin-bottom: 0.6rem;
    }
    .footer-link:hover {
        color: #ffc107; /* Amarillo corporativo */
        padding-left: 8px; /* Efecto de movimiento */
    }
    .social-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        color: #fff;
        margin-right: 10px;
        transition: all 0.3s;
        text-decoration: none;
        border: 1px solid transparent;
    }
    .social-btn:hover {
        background: #ffc107;
        color: #000;
        transform: translateY(-3px);
        border-color: #ffc107;
    }
    .btn-scroll-top {
        position: fixed;
        bottom: 25px;
        right: 25px;
        background: #ffc107;
        color: #000;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        display: none; 
        z-index: 9999;
        transition: all 0.3s;
        cursor: pointer;
    }
    .btn-scroll-top:hover {
        background: #fff;
        color: #000;
        transform: scale(1.1);
    }
</style>

<footer class="footer-custom pt-5 pb-3">
    <div class="container">
        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <h5 class="text-warning"><i class="bi bi-tools"></i> FERRETERÍA COMAS</h5>
                <p class="small">
                    Tu aliado confiable para la construcción y el hogar. 
                    Ofrecemos herramientas de calidad, materiales duraderos y asesoría experta para todos tus proyectos.
                </p>
                <div class="mt-3">
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <h5>Navegación</h5>
                <div class="row">
                    <div class="col-6">
                        <a href="dashboard.php" class="footer-link"><i class="bi bi-chevron-right small"></i> Inicio</a>
                        <a href="ventas.php" class="footer-link"><i class="bi bi-chevron-right small"></i> Nueva Venta</a>
                        <a href="productos.php" class="footer-link"><i class="bi bi-chevron-right small"></i> Inventario</a>
                    </div>
                    <div class="col-6">
                        <a href="clientes.php" class="footer-link"><i class="bi bi-chevron-right small"></i> Clientes</a>
                        <a href="reportes/reporte_ventas.php" class="footer-link"><i class="bi bi-chevron-right small"></i> Reportes</a>
                        <a href="../logout.php" class="footer-link text-danger"><i class="bi bi-box-arrow-right small"></i> Salir</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <h5>Contáctanos</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="bi bi-geo-alt-fill text-warning me-2"></i> Av. Túpac Amaru Km 11, Comas</li>
                    <li class="mb-2"><i class="bi bi-telephone-fill text-warning me-2"></i> +51 999 999 999</li>
                    <li class="mb-2"><i class="bi bi-envelope-fill text-warning me-2"></i> ventas@ferreteriacomas.com</li>
                    <li class="mb-0"><i class="bi bi-clock-fill text-warning me-2"></i> Lun-Sáb: 8am - 7pm</li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="text-center small text-muted">
            &copy; <?= date('Y') ?> <strong>Ferretería Comas</strong>. Todos los derechos reservados.
            <br>
            Sistema de Gestión v2.0 | Desarrollado con PHP & MySQL
        </div>
    </div>
</footer>

<button onclick="topFunction()" id="myBtn" class="btn-scroll-top" title="Volver arriba">
    <i class="bi bi-arrow-up-short"></i>
</button>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Obtener botón
    var mybutton = document.getElementById("myBtn");

    // Mostrar botón al bajar 200px
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // Al hacer clic, subir suavemente
    function topFunction() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    // Inicializar Tooltips de Bootstrap globalmente (para que funcionen en todas las tablas)
    document.addEventListener("DOMContentLoaded", function(){
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

</body>
</html>