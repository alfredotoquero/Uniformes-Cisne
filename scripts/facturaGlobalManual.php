<?php
// Tag largo a propósito: en CLI short_open_tag suele venir apagado y con "<?" el script
// imprimiría el código fuente en lugar de ejecutarlo.
/**
 * Emite una factura global al público en general con un total capturado a mano.
 *
 * Es la vía manual, para cuando el importe que hay que declarar lo trae el contador y no
 * sale de lo que el sistema tiene registrado. Timbra exactamente el mismo comprobante que
 * el cronjob (mismo receptor genérico, mismo concepto consolidado, mismo nodo
 * InformacionGlobal), pero el importe lo pones tú.
 *
 * El total que se captura es el TOTAL CON IVA, o sea lo que efectivamente se cobró. El
 * subtotal se obtiene dividiéndolo entre 1.16, igual que las globales que se venían
 * emitiendo a mano.
 *
 * Por default timbra contra el ambiente de PRUEBAS del PAC: el CFDI que regresa no tiene
 * validez fiscal, no se guarda en la base, no gasta folio y sus archivos van a
 * txts/pruebas/. Sirve para revisar el PDF y el XML antes de emitir el bueno. Hasta que no
 * se pasa --real no se toca nada.
 *
 * Uso (solo por línea de comandos):
 *   php scripts/facturaGlobalManual.php --formapago=tarjeta --total=3525.00 --mes=8 --anio=2026
 *       timbrado de PRUEBA, no escribe nada
 *
 *   php scripts/facturaGlobalManual.php --formapago=tarjeta --total=3525.00 --mes=8 --anio=2026 --real
 *       timbrado REAL: registra la factura, gasta el folio y guarda los archivos
 *
 * Opciones:
 *   --formapago=tarjeta|transferencia   forma de pago que consolida la factura
 *   --total=3525.00                     total con IVA incluido
 *   --mes=8 --anio=2026                 periodo que declara el nodo InformacionGlobal
 *   --emisor=RFC                        emisor (default GGU100112BE6)
 *   --serie=GA --folio=3                fuerza el consecutivo en lugar de tomarlo de
 *                                       temisores; con --folio el consecutivo del emisor
 *                                       NO se incrementa
 *   --marcar                            además marca los cobros del periodo como
 *                                       declarados en esta factura, para que el cronjob no
 *                                       los vuelva a tomar. Solo con --real, y solo si el
 *                                       total capturado corresponde a esos cobros
 *   --real                              timbra de verdad
 */

// El archivo vive dentro del document root, así que sin esto cualquiera podría dispararlo
// desde el navegador y timbrar un CFDI. Solo se permite por CLI.
if(php_sapi_name() !== "cli"){
    header("HTTP/1.1 403 Forbidden");
    exit("Este proceso solo puede ejecutarse por línea de comandos.");
}

$_SERVER['DOCUMENT_ROOT'] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/FacturasGlobales.php");

/**
 * Lee un argumento con valor de la forma --nombre=valor.
 */
function argumento($argv, $nombre, $default = null){
    foreach($argv as $arg){
        if(strpos($arg,"--".$nombre."=") === 0){
            return substr($arg,strlen($nombre)+3);
        }
    }
    return $default;
}

$real = in_array("--real", $argv);
$marcar = in_array("--marcar", $argv);
$rfcemisor = argumento($argv,"emisor","GGU100112BE6");
$serie = argumento($argv,"serie");
$folio = argumento($argv,"folio");
$formapago = strtolower(argumento($argv,"formapago",""));
$total = argumento($argv,"total");
$mes = argumento($argv,"mes",date("n",strtotime("first day of last month")));
$anio = argumento($argv,"anio",date("Y",strtotime("first day of last month")));

$rutalog = $_SERVER["DOCUMENT_ROOT"]."/txts/facturasGlobales.log";

/**
 * Deja rastro tanto en pantalla como en el log, en el mismo archivo que el cronjob: las
 * globales manuales y las automáticas son la misma numeración y conviene leerlas juntas.
 */
function registrar($rutalog, $linea){
    echo $linea."\n";
    file_put_contents($rutalog, date("Y-m-d H:i:s")." ".$linea."\n", FILE_APPEND);
}

$claseFacturasGlobales = new FacturasGlobales();

// La forma de pago se recibe por nombre porque un número suelto en la línea de comandos se
// presta a equivocarse: 3 y 5 son los ids internos, pero 03 y 04 son las claves del SAT y
// van al revés (03 es transferencia y 04 tarjeta).
$formaspago = array(
    "tarjeta" => FacturasGlobales::FORMAPAGO_TARJETA,
    "transferencia" => FacturasGlobales::FORMAPAGO_TRANSFERENCIA
);

if(!isset($formaspago[$formapago])){
    exit("Indica la forma de pago con --formapago=tarjeta o --formapago=transferencia\n");
}

if(!is_numeric($total) || (float)$total <= 0){
    exit("Indica el total con IVA incluido, por ejemplo --total=3525.00\n");
}

$idformapago = $formaspago[$formapago];
$total = round((float)$total,2);

$emisor = $claseFacturasGlobales->getEmisor($rfcemisor);

if(empty($emisor)){
    exit("No existe el emisor ".$rfcemisor."\n");
}

// Se muestra el desglose antes de mandar nada. Si el total se capturó como subtotal por
// error, aquí se ve de inmediato: el total de la factura no coincidiría con lo cobrado.
$desglose = $claseFacturasGlobales->desglose($total);

echo "\n";
echo "Emisor:      ".$emisor["rfc"]." - ".$emisor["razon_social"]."\n";
echo "Periodo:     ".sprintf("%02d",$mes)."/".$anio." (mensual)\n";
echo "Forma pago:  ".$formapago."\n";
echo "Subtotal:    $".number_format($desglose["subtotal_cfdi"],2)."\n";
echo "IVA ".FacturasGlobales::TASA_IVA."%:     $".number_format($desglose["iva"],2)."\n";
echo "Total:       $".number_format($desglose["total"],2)." (capturado $".number_format($total,2).")\n";
echo "Modo:        ".($real ? "REAL - se registra y gasta folio" : "PRUEBAS - no se guarda nada")."\n";
echo "\n";

// El desglose a seis decimales hace que el total caiga exacto en lo capturado. Si aun así
// no cuadra, el importe no se puede representar con esta tasa y hay que revisarlo antes de
// timbrar, porque el CFDI declararía una cifra distinta a la que se cobró.
if(abs($desglose["total"] - $total) > 0.001){
    echo "AVISO: el CFDI quedaría en $".number_format($desglose["total"] - $total,2)." de diferencia contra el total capturado\n\n";
}

$resultado = $claseFacturasGlobales->generarManual(array(
    "idemisor" => $emisor["idemisor"],
    "idformapago" => $idformapago,
    "mes" => $mes,
    "anio" => $anio,
    "total" => $total,
    "pruebas" => !$real,
    "marcar" => ($real && $marcar),
    "serie" => $serie,
    "folio" => $folio
));

if($resultado["respuesta"] != "OK"){
    registrar($rutalog, "ERROR en la global manual de ".$formapago." ".sprintf("%02d",$mes)."/".$anio.": ".$resultado["mensaje"]);
    exit(1);
}

if($resultado["pruebas"]){
    echo "CFDI DE PRUEBA generado (sin validez fiscal, no se registró nada)\n";
    echo "  Folio:    ".$resultado["serie"]."-".$resultado["folio"]." (el consecutivo no se movió)\n";
    echo "  UUID:     ".$resultado["uuid"]."\n";
    echo "  Total:    $".number_format($resultado["total"],2)."\n";
    echo "  Archivos: ".$resultado["archivos"].".pdf / .xml\n";
    echo "\nRevisa el PDF y, si está bien, vuelve a correr el mismo comando con --real\n";
    exit(0);
}

registrar($rutalog, "Global manual de ".$formapago." ".sprintf("%02d",$mes)."/".$anio.": ".$resultado["serie"]."-".$resultado["folio"]." (".$resultado["uuid"].") por $".number_format($resultado["total"],2).($marcar ? " - marcó ".$resultado["tickets"]." ticket(s) y ".$resultado["abonos"]." abono(s) por $".number_format($resultado["marcado"],2) : " - sin marcar cobros"));

if($marcar){
    // Con un total capturado a mano no hay garantía de que el dinero marcado sume ese
    // total. La diferencia se avisa para poder corregirla antes de que el mes se cierre.
    $diferencia = round($resultado["total"] - $resultado["marcado"],2);

    if(abs($diferencia) > 0.01){
        registrar($rutalog, "AVISO: la factura declara $".number_format($diferencia,2)." más de lo que suman los cobros marcados del periodo");
    }
}

echo "\nArchivos: ".$resultado["archivos"].".pdf / .xml\n";
