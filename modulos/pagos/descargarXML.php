<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pagos.php");

$idpago = isset($_GET["idpago"]) ? intval($_GET["idpago"]) : 0;

$clasePagos = new Pagos();
$datos = $clasePagos->getXML($idpago);

if ($datos["respuesta"] != "OK") {
    http_response_code(404);
    exit($datos["mensaje"]);
}

$ruta = $datos["xml"];

header("Content-Type: application/xml");
header("Content-Disposition: attachment; filename=\"" . basename($ruta) . "\"");
header("Content-Length: " . filesize($ruta));
header("Pragma: no-cache");
readfile($ruta);
exit;
