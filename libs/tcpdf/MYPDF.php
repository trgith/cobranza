<?php

/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 09/08/2016
 * Time: 11:54 AM
 */
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = ROOT.'views/img/trnetwork.png';
        $this->Image($image_file, 15, 10, 60, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        // Title
        $this->SetY(15);
        $this->SetX(120);
        $this->Cell(0, 10, 'Recibo de Pago Parcial', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('times', '', 8);
        // Page number
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        //Personal Footer
    }
    // Load table data from file
    public function LoadData($file) {
        // Read file lines
        $lines = file($file);
        $data = array();
        foreach($lines as $line) {
            $data[] = explode(';', chop($line));
        }
        return $data;
    }

    // Colored table
    public function ColoredTable($header,$data) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header
        $w = array(35, 15, 20, 25, 30, 30, 15, 20);
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        foreach($data as $row) {
            $this->Cell($w[0], 6, $row['nombre'], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, number_format($row['taras']), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row['producto'], 'LR', 0, 'R', $fill);
            $this->Cell($w[3], 6, "$".number_format($row['precio']), 'LR', 0, 'R', $fill);
            $this->Cell($w[4], 6, $row['cantidad_estado']." con: ".$row['estado'], 'LR', 0, 'R', $fill);
            $this->Cell($w[5], 6, $row['tipo_venta'], 'LR', 0, 'R', $fill);
            $this->Cell($w[6], 6, "$".number_format($row['total']), 'LR', 0, 'R', $fill);
            $this->Cell($w[7], 6, $row['nuevaFecha'], 'LR', 0, 'R', $fill);
            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}