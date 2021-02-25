<?php
/**
 * Created by PhpStorm.
 * User: FIREWORLD
 * Date: 09/07/2018
 * Time: 03:22 PM
 */

class Pdf1 extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo
        $logo = ROOT."public/img/logo.png";
        $this->Image($logo,10,8,33);
        // Arial bold 15
        $this->SetFont('Arial','B',16);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $title = 'Comprobante de corte de caja';
        $w = $this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        // Colores de los bordes, fondo y texto
        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        // Ancho del borde (1 mm)
        $this->SetLineWidth(1);
        // Título
        $this->Cell($w,9,$title,1,1,'C',true);
        // Salto de línea
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo(),0,0,'C');
    }
}