<?php
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$titulo = "Factura Electrónica";
$contenido = '
<p>Estimado/a cliente:</p>

<p>
Gracias por su compra.  
Su <strong>factura electrónica (CFDI)</strong> ha sido generada correctamente.
</p>

<div class="info-box">
<p><strong>Folio:</strong> '.$folio.'</p>
<p><strong>Fecha de emisión:</strong> '.date("d",strtotime($fecha)).' de '.$meses[date("n",strtotime($fecha))-1].' de '.date("Y",strtotime($fecha)).'</p>
<p><strong>Monto:</strong> $'.number_format($total,2).'</p>
</div>

<p>
Se adjuntan los archivos correspondientes en formato  
<strong>PDF</strong> y <strong>XML</strong>.
</p>

<p style="margin-top: 20px;">
Gracias por su preferencia.  
<br><br>
<small>Favor de no contestar a este correo ya que las respuestas no se supervisan.</small>
</p>';
?>