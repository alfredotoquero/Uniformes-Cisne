<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Facturas.php");

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

$claseFacturas = new Facturas();
$datos = $claseFacturas->getZIPData($idfactura);

if ($datos["respuesta"] != "OK") {
    http_response_code(404);
    exit($datos["mensaje"]);
}

$cliente_limpio = preg_replace('/[\/:*?"<>|\\\\]/', '', $datos["cliente"]);
$nombre_zip = $datos["serie"] . "-" . $datos["folio"] . " " . trim($cliente_limpio) . ".zip";

$tmp = tempnam(sys_get_temp_dir(), "factura_");

$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    exit("No se pudo generar el archivo ZIP");
}

$zip->addFile($datos["pdf_path"], $datos["uuid"] . ".pdf");
$zip->addFile($datos["xml_path"], $datos["uuid"] . ".xml");
$zip->close();

header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"" . $nombre_zip . "\"");
header("Content-Length: " . filesize($tmp));
header("Pragma: no-cache");
readfile($tmp);
unlink($tmp);
exit;
