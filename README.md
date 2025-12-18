# 🛠️ Sistema de Gestión - Ferretería Comas

Sistema web integral para la gestión de inventario, clientes y ventas (Punto de Venta - POS) diseñado para ferreterías. Desarrollado con PHP nativo y MySQL, enfocado en la seguridad, rapidez y una interfaz moderna.

![Estado del Proyecto](https://img.shields.io/badge/Estado-Terminado-success)
![Versión](https://img.shields.io/badge/Versión-2.0-blue)

## 📋 Características Principales

* **🔐 Seguridad:** Login con protección contra fuerza bruta, inyección SQL y CSRF (Tokens).
* **📦 Inventario:** Gestión de productos (CRUD) con alertas visuales de stock bajo y crítico.
* **🛒 Punto de Venta (POS):**
    * Buscador inteligente de productos y clientes (Select2).
    * Carrito de compras dinámico.
    * Control de stock en tiempo real con **Transacciones ACID** (evita ventas sin stock).
* **👥 Gestión de Clientes:** Directorio de clientes con historial de compras.
* **📊 Dashboard:** Gráficos estadísticos de ventas y productos más vendidos (Chart.js).
* **📄 Reportes:** Generación automática de reportes en PDF (FPDF).

## 🚀 Tecnologías Utilizadas

* **Backend:** PHP 8+ (PDO, Funciones Planas, POO para PDF).
* **Base de Datos:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3, Bootstrap 5.3.
* **JavaScript:**
    * jQuery (Core).
    * SweetAlert2 (Alertas modernas).
    * Select2 (Buscadores avanzados).
    * Chart.js (Gráficos).

## 🔧 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en tu servidor local (XAMPP, WAMP, Laragon).

### 1. Clonar el repositorio
```bash
git clone [https://github.com/hansmarinos1/Ferreteria_web.git](https://github.com/hansmarinos1/Ferreteria_web.git)

