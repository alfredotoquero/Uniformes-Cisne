<?php
/**
 * Repara los datos fiscales de las facturas que quedaron sin receptor en base de datos.
 *
 * Pedidos::facturarPedido no guardaba razonsocial/rfc cuando el pedido no tenía cliente,
 * así que esas facturas quedaron sin idrazonsocial y sin datos en texto. La información
 * correcta sí está en el CFDI timbrado, en el nodo cfdi:Receptor, de donde este script la
 * recupera.
 *
 * Requiere haber ejecutado antes sql/facturas_datos_fiscales.sql.
 *
 * Uso (por CLI, desde el servidor):
 *     php scripts/repararDatosFiscalesFacturas.php            -> simulación, no escribe nada
 *     php scripts/repararDatosFiscalesFacturas.php --aplicar  -> aplica los UPDATE
 */

$_SERVER["DOCUMENT_ROOT"] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

$aplicar = in_array("--aplicar", $argv);

/**
 * Los nombres se mandaron al timbrador con utf8_decode, así que el XML puede traerlos en
 * ISO-8859-1 aunque se declare UTF-8. Se normaliza para no guardar basura en la BD.
 */
function normalizar($texto){
    $texto = trim($texto);
    return (mb_check_encoding($texto, "UTF-8")) ? $texto : utf8_encode($texto);
}

/**
 * El XML se escribe reemplazando "&" por "_" en el RFC del emisor, pero otras partes del
 * sistema lo leen sin reemplazo, así que se prueban las dos rutas.
 */
function rutaXML($rfcemisor, $uuid){
    $rutas = array(
        $_SERVER["DOCUMENT_ROOT"]."/emisores/".str_replace("&", "_", $rfcemisor)."/facturas/".$uuid.".xml",
        $_SERVER["DOCUMENT_ROOT"]."/emisores/".$rfcemisor."/facturas/".$uuid.".xml"
    );

    foreach($rutas as $ruta){
        if(file_exists($ruta)){
            return $ruta;
        }
    }

    return false;
}

// Solo las facturas que no tienen NINGUNO de los datos: ni la relación ni el texto.
// Una factura con razonsocial o rfc ya cargados no se toca.
$query = "
select
    a.idfactura,
    a.uuid,
    a.serie,
    a.folio,
    b.rfc as rfc_emisor
from
    tfacturas a
left join
    temisores b
on
    b.idemisor = a.idemisor
where
    (a.idrazonsocial is null or a.idrazonsocial = 0) and
    (a.razonsocial is null or a.razonsocial = '') and
    (a.rfc is null or a.rfc = '') and
    a.uuid is not null and
    a.uuid <> ''
order by
    a.idfactura";
$result = mysqli_query($con, $query);

if(!$result){
    exit("Error al consultar las facturas: ".mysqli_error($con)."\n");
}

$total = mysqli_num_rows($result);
$reparadas = 0;
$sinxml = 0;
$errores = 0;

echo ($aplicar ? "MODO APLICAR" : "MODO SIMULACIÓN (usa --aplicar para escribir)")."\n";
echo "Facturas candidatas: ".$total."\n\n";

while($factura = mysqli_fetch_assoc($result)){
    $etiqueta = "#".$factura["idfactura"]." ".$factura["serie"]."-".$factura["folio"]." (".$factura["uuid"].")";

    $ruta = rutaXML($factura["rfc_emisor"], $factura["uuid"]);

    if($ruta === false){
        echo "[SIN XML]  ".$etiqueta."\n";
        $sinxml++;
        continue;
    }

    $xml = @simplexml_load_file($ruta);

    if($xml === false){
        echo "[XML MAL]  ".$etiqueta."\n";
        $errores++;
        continue;
    }

    $namespaces = $xml->getNamespaces(true);
    $ns = isset($namespaces["cfdi"]) ? $namespaces["cfdi"] : null;
    $nodo = ($ns) ? $xml->children($ns)->Receptor : $xml->Receptor;

    if(!isset($nodo[0])){
        echo "[SIN NODO] ".$etiqueta." no tiene cfdi:Receptor\n";
        $errores++;
        continue;
    }

    $atributos = $nodo[0]->attributes();

    $razonsocial   = normalizar((string)$atributos["Nombre"]);
    $rfc           = normalizar((string)$atributos["Rfc"]);
    $codigo_postal = normalizar((string)$atributos["DomicilioFiscalReceptor"]);
    $regimenfiscal = normalizar((string)$atributos["RegimenFiscalReceptor"]);
    $usocfdi       = normalizar((string)$atributos["UsoCFDI"]);

    if($rfc == "" || $razonsocial == ""){
        echo "[INCOMPLETO] ".$etiqueta." el XML no trae Rfc/Nombre del receptor\n";
        $errores++;
        continue;
    }

    echo ($aplicar ? "[REPARADA] " : "[PENDIENTE]")." ".$etiqueta." -> ".$rfc." | ".$razonsocial." | CP ".$codigo_postal." | Reg ".$regimenfiscal." | Uso ".$usocfdi."\n";

    if(!$aplicar){
        $reparadas++;
        continue;
    }

    $query = "
    update
        tfacturas
    set
        razonsocial = '".mysqli_real_escape_string($con, $razonsocial)."',
        rfc = '".mysqli_real_escape_string($con, $rfc)."',
        codigo_postal = '".mysqli_real_escape_string($con, $codigo_postal)."',
        regimenfiscal = '".mysqli_real_escape_string($con, $regimenfiscal)."',
        usocfdi = '".mysqli_real_escape_string($con, $usocfdi)."'
    where
        idfactura = '".$factura["idfactura"]."' and
        (idrazonsocial is null or idrazonsocial = 0) and
        (razonsocial is null or razonsocial = '') and
        (rfc is null or rfc = '')";

    if(mysqli_query($con, $query)){
        $reparadas++;
    }else{
        echo "[ERROR SQL] ".$etiqueta." ".mysqli_error($con)."\n";
        $errores++;
    }
}

echo "\n";
echo "Reparadas: ".$reparadas."\n";
echo "Sin XML:   ".$sinxml."\n";
echo "Errores:   ".$errores."\n";

if(!$aplicar){
    echo "\nNo se escribió nada. Vuelve a correr con --aplicar para guardar los cambios.\n";
}
