<?php
// Asegúrate de que la ruta a fpdf.php sea correcta
require_once 'fpdf/fpdf.php';

class ReportePDF extends FPDF {
    
    // Cabecera de página (Se repite en todas las hojas)
    function Header() {
        // Logo (si tienes uno, descomenta y ajusta la ruta)
        // $this->Image('../assets/img/logo.png', 10, 8, 33);
        
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(30, 10, utf8_decode('FERRETERÍA COMAS'), 0, 0, 'C');
        
        // Salto de línea
        $this->Ln(10);
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('RUC: 20123456789 | Dirección: Av. Túpac Amaru Km 11'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Tel: (01) 555-1234 | Email: contacto@ferreteria.com'), 0, 1, 'C');
        
        // Línea divisoria
        $this->Ln(5);
        $this->SetDrawColor(180, 180, 180);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    // Pie de página (Se repite en todas las hojas)
    function Footer() {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
?>