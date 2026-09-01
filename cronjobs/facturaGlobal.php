<?php
// Tag largo a propósito: en CLI short_open_tag suele venir apagado y con "<?" el cron
// imprimiría el código fuente en lugar de ejecutarlo.
/**
 * Emite la factura global mensual de operaciones con el público en general.
 *
 * Consolida todo el dinero que entró por tarjeta y por transferencia durante el mes y que
 * nunca se amparó con un CFDI: los tickets de mostrador que nadie pidió facturados y los
 * abonos a pedidos que cayeron sobre la parte del pedido que todavía no se factura. Emite
 * una factura por forma de pago (el CFDI declara una sola FormaPago), cada una con un
 * único concepto por el total del periodo.
 *
 * Lo que se declara en cada global queda marcado en tformaspagoticket.idfacturaglobal y
 * tformaspagopedido.idfacturaglobal, así que una segunda corrida del mismo mes no vuelve a
 * tomar el mismo dinero. Requiere la migración sql/facturas_globales.sql.
 *
 * Uso (solo por línea de comandos):
 *   php cronjobs/facturaGlobal.php                    simulación del mes anterior: reporta
 *                                                     qué facturaría y por cuánto, sin
 *                                                     timbrar ni escribir nada
 *   php cronjobs/facturaGlobal.php --aplicar          timbra las facturas del mes anterior
 *   php cronjobs/facturaGlobal.php --mes=7 --anio=2026
 *                                                     trabaja sobre ese periodo en lugar
 *                                                     del mes anterior (para reprocesos)
 *   php cronjobs/facturaGlobal.php --emisor=RFC       usa otro emisor
 *   php cronjobs/facturaGlobal.php --tasa=16          tasa de IVA (default 8)
 *   php cronjobs/facturaGlobal.php --idusuario=N      usuario al que quedan acreditadas las
 *                                                     facturas (default IDUSUARIO_FACTURAS).
 *                                                     tfacturas.idusuario es llave foránea a
 *                                                     tusuarios, así que tiene que existir
 *   php cronjobs/facturaGlobal.php --detalle          lista documento por documento lo que
 *                                                     entraría (útil en simulación)
 *
 * Crontab sugerido (día 2 de cada mes, para dar margen a que cierre el último corte):
 *   0 6 2 * * /usr/bin/php /home/jorgur2/uniformescisne.mx/1.uniformescisne.mx/cronjobs/facturaGlobal.php --aplicar
 *
 * Cuidado con cancelar pagos de un mes ya declarado: al cancelar un pago se borran sus
 * renglones de tformaspagopedido, así que la global se queda declarando dinero que ya no
 * existe. En ese caso hay que cancelar la global y reemitirla; el resumen de cada corrida
 * imprime el comparativo para detectarlo.
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

$aplicar = in_array("--aplicar", $argv);
$detalle = in_array("--detalle", $argv);
$rfcemisor = argumento($argv,"emisor","GGU100112BE6");

// La factura queda acreditada a un usuario real: tfacturas.idusuario tiene llave foránea a
// tusuarios y no existe un "usuario sistema" al que colgarla. El cronjob corre desatendido,
// así que se fija aquí en lugar de pedirse en cada corrida.
define("IDUSUARIO_FACTURAS", 1);

$idusuario = argumento($argv,"idusuario",IDUSUARIO_FACTURAS);
$tasa = argumento($argv,"tasa",FacturasGlobales::TASA_IVA);

// Por default el mes anterior: el cronjob corre a principios de mes sobre el periodo que
// acaba de cerrar.
$mes = argumento($argv,"mes",date("n",strtotime("first day of last month")));
$anio = argumento($argv,"anio",date("Y",strtotime("first day of last month")));

$rutalog = $_SERVER["DOCUMENT_ROOT"]."/txts/facturasGlobales.log";

/**
 * Deja rastro tanto en pantalla como en el log. El cronjob corre desatendido y emite CFDI,
 * así que el log se anexa en lugar de sobreescribirse.
 */
function registrar($rutalog, $linea){
    echo $linea."\n";
    file_put_contents($rutalog, date("Y-m-d H:i:s")." ".$linea."\n", FILE_APPEND);
}

$claseFacturasGlobales = new FacturasGlobales();

$emisor = $claseFacturasGlobales->getEmisor($rfcemisor);

if(empty($emisor)){
    registrar($rutalog, "ERROR: no existe el emisor ".$rfcemisor);
    exit(1);
}

$periodo = $claseFacturasGlobales->periodo($mes,$anio);
$formaspago = $claseFacturasGlobales->formasPago();

registrar($rutalog, "=== Factura global ".$periodo["mes"]."/".$periodo["anio"]." - ".$emisor["rfc"]." al ".$tasa."% de IVA (".($aplicar ? "timbrando" : "simulación").")");

foreach($formaspago as $formapago){
    $idformapago = $formapago["idformapago"];

    $resultado = $claseFacturasGlobales->generar(array(
        "idemisor" => $emisor["idemisor"],
        "idusuario" => $idusuario,
        "idformapago" => $idformapago,
        "tasaiva" => $tasa,
        "mes" => $mes,
        "anio" => $anio,
        "simular" => !$aplicar
    ));

    if($resultado["respuesta"] != "OK"){
        registrar($rutalog, "ERROR en ".$formapago["nombre"].": ".$resultado["mensaje"]);
        continue;
    }

    if($resultado["timbrada"]){
        registrar($rutalog, $formapago["nombre"].": ".$resultado["serie"]."-".$resultado["folio"]." (".$resultado["uuid"].") por $".number_format($resultado["total"],2)." - ".$resultado["tickets"]." ticket(s) y ".$resultado["abonos"]." abono(s), cobrado $".number_format($resultado["cobrado"],2));

        // Un centavo de diferencia es el redondeo del subtotal y es normal; más que eso
        // significa que el timbrador calculó algo distinto y hay que revisar el CFDI.
        $diferencia = round($resultado["total"] - $resultado["cobrado"],2);
        if(abs($diferencia) > 0.01){
            registrar($rutalog, "AVISO ".$formapago["nombre"].": el CFDI quedó en $".number_format($diferencia,2)." de diferencia contra lo cobrado");
        }
    }else{
        $cobros = $resultado["cobros"];

        if($cobros["total"] <= 0){
            registrar($rutalog, $formapago["nombre"].": nada que declarar en el periodo");
        }else{
            registrar($rutalog, $formapago["nombre"].": ".(!$aplicar ? "se facturaría " : "")."$".number_format($cobros["total"],2)." (subtotal $".number_format($resultado["subtotal"],2).") - ".$cobros["tickets"]." ticket(s) por $".number_format($cobros["total_tickets"],2)." y ".$cobros["abonos"]." abono(s) por $".number_format($cobros["total_abonos"],2).", a partir del folio ".$resultado["serie"]."-".$resultado["folio"]);
        }
    }

    if($detalle){
        foreach($claseFacturasGlobales->detalleCobros($idformapago,$periodo) as $renglon){
            registrar($rutalog, "    ".$formapago["nombre"]." ".$renglon["origen"]." ".$renglon["documento"]." ".$renglon["fecha"]." $".number_format($renglon["monto"],2));
        }
    }

    // No son un error de la global: es dinero que otra parte del sistema dejó a medias y
    // que por prudencia se queda fuera para no declararlo dos veces.
    $revisar = $claseFacturasGlobales->abonosPendientesRevision($idformapago,$periodo);

    foreach($revisar as $pendiente){
        registrar($rutalog, "REVISAR ".$formapago["nombre"].": abono de $".number_format($pendiente["monto"],2)." del pedido ".$pendiente["idpedido"]." (".$pendiente["fecha"].") queda fuera de la global - ".$pendiente["motivo"]." ".$pendiente["serie"]."-".$pendiente["folio"]);
    }
}

registrar($rutalog, "=== Fin");
