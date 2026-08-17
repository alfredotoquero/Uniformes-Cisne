<?php
/**
 * Recalcula el saldo de las facturas vigentes a partir de lo que realmente las amortizó:
 * los pagos registrados en tpagosfacturas y las notas de crédito activas.
 *
 * Existen facturas cuyo saldo quedó desfasado por refacturaciones anteriores al backfill de
 * sql/complementos_pago.sql: el complemento descontó el saldo de la factura original, esa
 * factura se canceló y se refacturó, y la factura nueva nació con el saldo completo. Después
 * el backfill movió la fila de tpagosfacturas a la factura vigente (que es lo correcto: el
 * pago del cliente sigue vivo y ahora corresponde a esa factura), pero el saldo nunca se
 * movió con ella.
 *
 * La fórmula es fiable porque el saldo solo se mueve en dos lugares y ambos dejan rastro:
 * Pagos::timbrarPago lo descuenta al insertar en tpagosfacturas, y Pagos::revertirEfectoPago
 * lo devuelve al BORRAR esa fila cuando se cancela el pago. Que la fila exista significa que
 * su monto debe estar descontado, sin importar en qué estado esté el complemento: cancelar el
 * CFDI del complemento no devuelve el saldo, eso pasa hasta que se cancela el pago.
 *
 * Solo toca facturas activas (status = 1). Las canceladas y las que están en proceso de
 * cancelación se dejan como están: su saldo ya no representa nada por cobrar y "corregirlo"
 * sería devolverles un saldo que nadie va a cobrar.
 *
 * Uso (por CLI, desde el servidor):
 *     php scripts/corregirSaldosFacturas.php            -> simulación, no escribe nada
 *     php scripts/corregirSaldosFacturas.php --aplicar  -> aplica los UPDATE
 */

$_SERVER["DOCUMENT_ROOT"] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

$aplicar = in_array("--aplicar", $argv);

echo ($aplicar ? "MODO APLICAR" : "MODO SIMULACIÓN (usa --aplicar para escribir)")."\n\n";

$query = "
select
    a.idfactura,
    a.serie,
    a.folio,
    a.total,
    a.saldo,
    coalesce((
        select sum(b.monto)
        from tpagosfacturas b
        where b.idfactura = a.idfactura
    ),0) as amortizado,
    coalesce((
        select sum(c.total)
        from tnotascredito c
        where c.idfactura = a.idfactura and c.status = 1
    ),0) as notascredito
from
    tfacturas a
where
    a.status = 1
order by
    a.idfactura";
$result = mysqli_query($con, $query);

if(!$result){
    exit("Error al consultar las facturas: ".mysqli_error($con)."\n");
}

$revisadas = 0;
$corregidas = 0;
$errores = 0;

while($factura = mysqli_fetch_assoc($result)){
    $revisadas++;

    $total = (float)$factura["total"];
    $saldo = round((float)$factura["saldo"], 2);

    // Nunca por debajo de cero: un redondeo de centavos no debe dejar la factura en negativo
    $esperado = round(max(0, $total - (float)$factura["amortizado"] - (float)$factura["notascredito"]), 2);

    if(abs($saldo - $esperado) <= 0.02){
        continue;
    }

    $etiqueta = "#".$factura["idfactura"]." ".$factura["serie"]."-".$factura["folio"];

    echo ($aplicar ? "[CORREGIDA] " : "[PENDIENTE] ").$etiqueta
        ." | total $".number_format($total,2)
        ." | pagos $".number_format($factura["amortizado"],2)
        ." | notas de crédito $".number_format($factura["notascredito"],2)
        ." | saldo $".number_format($saldo,2)." -> $".number_format($esperado,2)."\n";

    if(!$aplicar){
        $corregidas++;
        continue;
    }

    $query = "
    update
        tfacturas
    set
        saldo = '".$esperado."'
    where
        idfactura = '".(int)$factura["idfactura"]."' and
        status = 1";

    if(mysqli_query($con, $query)){
        $corregidas++;
    }else{
        echo "[ERROR SQL] ".$etiqueta." ".mysqli_error($con)."\n";
        $errores++;
    }
}

echo "\n";
echo "Facturas revisadas:  ".$revisadas."\n";
echo "Saldos ".($aplicar ? "corregidos" : "por corregir").": ".$corregidas."\n";
echo "Errores:             ".$errores."\n";

if(!$aplicar){
    echo "\nNo se escribió nada. Vuelve a correr con --aplicar para guardar los cambios.\n";
}
