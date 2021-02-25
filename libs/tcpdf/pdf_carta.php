<?php
/**
 * Created by PhpStorm.
 * User: desarrollo2
 * Date: 27/02/20
 * Time: 8:31
 */

class pdf_carta extends TCPDF
{
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('helvetica', '', 8);

        $html = '<span style="font-size: 10px"> Valverde Jurídico<br>
                        <b>Dirección:</b> BLVD. HERMANOS SERDAN No. 292<br>
                        <b>Código postal y municipio:</b> 72060 PUEBLA<br>
                        <b>Correo: </b><a href=»dcobranza@trnetwork.com.mx»>dcobranza@trnetwork.com.mx</a></span>';

        $y = $this->GetY();

        $this->writeHTMLCell(160, 0, 30, $y, $html, '', 1, 0, true, 'L', true);

        $html = '<span style="font-size: 10px"> TR network<br>
                        <b>Dirección:</b> BLVD. SAN FELIPE No.224<br>
                        <b>Código postal y municipio:</b> 72040 PUEBLA<br>
                        <b>Correo: </b><a href=»direccion@trnetwork.com.mx»>direccion@trnetwork.com.mx</a></span>';

        $this->writeHTMLCell(160, 0, 120, $y, $html, '', 1, 0, true, 'L', true);
    }
}