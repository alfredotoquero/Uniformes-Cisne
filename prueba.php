<?php
$folio = "A-3";
$fecha = "2025-11-28";
$total = 4500;
include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/envioFactura.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/plantillas/correo/base.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Correos.php");

$c = new Correos();

$respuesta = $c->enviarCorreo(array(
    "asunto" => "Envío de factura",
    "mensaje" => $cuerpo,
    "correos" => array(
        "alfredo.toquerou@gmail.com"
    )
));

var_dump($respuesta);
?>