<?php
/**
 * Completa los datos fiscales que faltan en las facturas que sí guardaron razonsocial y rfc
 * pero no el resto del receptor (código postal, régimen fiscal y uso del CFDI).
 *
 * Es el caso de las facturas de tickets de la tienda (Tickets::facturarTicket solo guardaba
 * esos dos campos) y de cualquier factura sin cliente anterior a las columnas nuevas. La
 * información completa está en el CFDI timbrado, en el nodo cfdi:Receptor.
 *
 * Complementa a repararDatosFiscalesFacturas.php, que atiende el caso contrario: facturas sin
 * ningún dato del receptor. Este script NO toca razonsocial ni rfc, y solo escribe las
 * columnas que estén vacías; lo que ya tenga valor se respeta.
 *
 * Requiere haber ejecutado antes sql/facturas_datos_fiscales.sql.
 *
 * Uso (por CLI, desde el servidor):
 *     php scripts/completarDatosFiscalesFacturas.php            -> simulación, no escribe nada
 *     php scripts/completarDatosFiscalesFacturas.php --aplicar  -> aplica los UPDATE
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

// Facturas sin razón social relacionada que ya tienen el receptor en texto (razonsocial o rfc)
// pero les falta al menos uno de los otros tres datos
$query = "
select
    a.idfactura,
    a.uuid,
    a.serie,
    a.folio,
    a.codigo_postal,
    a.regimenfiscal,
    a.usocfdi,
    b.rfc as rfc_emisor
from
    tfacturas a
left join
    temisores b
on
    b.idemisor = a.idemisor
where
    (a.idrazonsocial is null or a.idrazonsocial = 0) and
    (
        (a.razonsocial is not null and a.razonsocial <> '') or
        (a.rfc is not null and a.rfc <> '')
    ) and
    (
        (a.codigo_postal is null or a.codigo_postal = '') or
        (a.regimenfiscal is null or a.regimenfiscal = '') or
        (a.usocfdi is null or a.usocfdi = '')
    ) and
    a.uuid is not null and
    a.uuid <> ''
order by
    a.idfactura";
$result = mysqli_query($con, $query);

if(!$result){
    exit("Error al consultar las facturas: ".mysqli_error($con)."\n");
}

$total = mysqli_num_rows($result);
$completadas = 0;
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

    // Solo se rellena lo que esté vacío: un dato ya capturado no se sobreescribe con el XML
    $valores = array(
        "codigo_postal" => normalizar((string)$atributos["DomicilioFiscalReceptor"]),
        "regimenfiscal" => normalizar((string)$atributos["RegimenFiscalReceptor"]),
        "usocfdi"       => normalizar((string)$atributos["UsoCFDI"])
    );

    $sets = array();
    $resumen = array();

    foreach($valores as $columna => $valor){
        if(trim($factura[$columna]) !== "" || $valor === ""){
            continue;
        }

        $sets[] = $columna." = '".mysqli_real_escape_string($con, $valor)."'";
        $resumen[] = $columna."=".$valor;
    }

    if(empty($sets)){
        echo "[SIN DATOS] ".$etiqueta." el XML no aporta ninguno de los campos faltantes\n";
        $errores++;
        continue;
    }

    echo ($aplicar ? "[COMPLETADA]" : "[PENDIENTE] ")." ".$etiqueta." -> ".implode(" | ", $resumen)."\n";

    if(!$aplicar){
        $completadas++;
        continue;
    }

    $query = "
    update
        tfacturas
    set
        ".implode(",\n        ", $sets)."
    where
        idfactura = '".$factura["idfactura"]."' and
        (idrazonsocial is null or idrazonsocial = 0)";

    if(mysqli_query($con, $query)){
        $completadas++;
    }else{
        echo "[ERROR SQL] ".$etiqueta." ".mysqli_error($con)."\n";
        $errores++;
    }
}

echo "\n";
echo "Completadas: ".$completadas."\n";
echo "Sin XML:     ".$sinxml."\n";
echo "Errores:     ".$errores."\n";

if(!$aplicar){
    echo "\nNo se escribió nada. Vuelve a correr con --aplicar para guardar los cambios.\n";
}
