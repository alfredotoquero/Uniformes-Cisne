<?php
// Tag largo a propósito: en CLI short_open_tag suele venir apagado y con "<?" el cron
// imprimiría el código fuente en lugar de ejecutarlo.
/**
 * Reconsulta ante el SAT los documentos que quedaron en proceso de cancelación.
 *
 * Cuando el timbrador responde statusCFDI 201 la cancelación no es definitiva: el receptor
 * tiene 3 días para aceptarla o rechazarla. Este barrido pregunta por cada documento atorado
 * en ese limbo y aplica el desenlace (cancelado, sigue en proceso, o rechazado y vuelve a
 * activo). Cubre facturas y complementos de pago de los dos proyectos, porque comparten la
 * misma base de datos: la tienda solo necesita el botón manual del listado.
 *
 * Uso (solo por línea de comandos):
 *   php cronjobs/verificarStatusCanceladas.php              consulta al SAT y reporta el
 *                                                           desenlace de cada documento, sin
 *                                                           modificar ningún registro
 *   php cronjobs/verificarStatusCanceladas.php --aplicar    además aplica los cambios
 *
 * Las dos corridas consultan al SAT, así que el reporte de la simulación es exactamente lo
 * que haría la corrida real. La consulta es de solo lectura ante el SAT.
 */

// El archivo vive dentro del document root, así que sin esto cualquiera podría dispararlo
// desde el navegador y mover estatus fiscales. Solo se permite por CLI.
if(php_sapi_name() !== "cli"){
    header("HTTP/1.1 403 Forbidden");
    exit("Este proceso solo puede ejecutarse por línea de comandos.");
}

$_SERVER['DOCUMENT_ROOT'] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pagos.php");

$aplicar = in_array("--aplicar", $argv);
$rutalog = $_SERVER["DOCUMENT_ROOT"]."/txts/verificacionCancelaciones.log";

$claseFacturas = new Facturas();
$clasePagos = new Pagos();

/**
 * Deja rastro del barrido tanto en pantalla como en el log. El cronjob corre desatendido y
 * cambia estatus fiscales, así que el log se anexa en lugar de sobreescribirse.
 */
function registrar($rutalog, $linea){
    echo $linea."\n";
    file_put_contents($rutalog, date("Y-m-d H:i:s")." ".$linea."\n", FILE_APPEND);
}

$facturas = $claseFacturas->getFacturasEnProcesoCancelacion();
$pagos = $clasePagos->getPagosEnProcesoCancelacion();

registrar($rutalog, "=== Inicio del barrido (".($aplicar ? "aplicando" : "simulación").") - ".count($facturas)." factura(s) y ".count($pagos)." complemento(s) en proceso de cancelación");

foreach($facturas as $factura){
    $resultado = $claseFacturas->verificarEstatusSAT(array(
        "idfactura" => $factura["idfactura"],
        "simular" => !$aplicar
    ));

    if($resultado["respuesta"] == "OK"){
        registrar($rutalog, "Factura ".$factura["idfactura"]." (".$resultado["uuid"]."): ".$resultado["resultado"]." - ".$resultado["mensaje"]);
    }else{
        registrar($rutalog, "ERROR en factura ".$factura["idfactura"].": ".$resultado["mensaje"]);
    }
}

foreach($pagos as $pago){
    $resultado = $clasePagos->verificarEstatusSAT(array(
        "idpago" => $pago["idpago"],
        "simular" => !$aplicar
    ));

    if($resultado["respuesta"] == "OK"){
        registrar($rutalog, "Complemento del pago ".$pago["idpago"]." (".$resultado["uuid"]."): ".$resultado["resultado"]." - ".$resultado["mensaje"]);
    }else{
        registrar($rutalog, "ERROR en el complemento del pago ".$pago["idpago"].": ".$resultado["mensaje"]);
    }
}

registrar($rutalog, "=== Fin del barrido");
