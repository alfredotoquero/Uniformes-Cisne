<?php
/**
 * Borra de tpagosfacturas las amortizaciones que el backfill de sql/complementos_pago.sql
 * atribuyó a la factura equivocada.
 *
 * Ese backfill ligó cada complemento ya timbrado con la factura que en ese momento tenía el
 * pedido (tpedidos.idfactura). En los pedidos que se refacturaron, esa factura es una POSTERIOR
 * al complemento: la que el complemento amortizó de verdad se canceló y se desligó del pedido.
 * El resultado son filas de tpagosfacturas donde el complemento se timbró ANTES que la factura,
 * lo cual es imposible, y que ensucian el listado (la factura se marca con pagos aunque su saldo
 * esté completo).
 *
 * Además son un riesgo: si alguno de esos pagos se cancela, Pagos::revertirEfectoPago le SUMA
 * el monto al saldo de una factura a la que nunca se lo restó, dejándola con saldo mayor a su
 * total. Borrar la fila es lo mismo que hace revertirEfectoPago al cancelar un pago.
 *
 * El script NO toca el saldo de ninguna factura: solo borra filas cuya amortización nunca se
 * aplicó al saldo. Antes de borrar lo verifica factura por factura (ver más abajo) y salta las
 * que no cumplan, para revisarlas a mano.
 *
 * Uso (por CLI, desde el servidor):
 *     php scripts/limpiarAmortizacionesBackfill.php            -> simulación, no borra nada
 *     php scripts/limpiarAmortizacionesBackfill.php --aplicar  -> aplica los DELETE
 */

$_SERVER["DOCUMENT_ROOT"] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

$aplicar = in_array("--aplicar", $argv);

echo ($aplicar ? "MODO APLICAR" : "MODO SIMULACIÓN (usa --aplicar para borrar)")."\n\n";

// Amortizaciones imposibles: el complemento se timbró antes de que existiera la factura.
//
// Los dos timbrados no son comparables al minuto: tfacturas.timbrado es la fecha que
// devuelve el timbrador y tpagos.timbrado es el NOW() del servidor, que va una hora atrás.
// Por eso se exige un margen de 24 horas, muy por encima del desfase, para no confundir un
// complemento legítimo timbrado el mismo día con uno mal atribuido (los del backfill llevan
// días de diferencia). Si a alguno de los dos timbrados le falta fecha, la fila no se toca.
$query = "
select
    a.idpagofactura,
    a.idpago,
    a.idfactura,
    a.monto,
    a.registro,
    b.serie as serie_pago,
    b.folio as folio_pago,
    b.uuid as uuid_pago,
    b.timbrado as timbrado_pago,
    b.status as status_pago,
    c.serie,
    c.folio,
    c.total,
    c.saldo,
    c.timbrado as timbrado_factura
from
    tpagosfacturas a
inner join
    tpagos b
on
    b.idpago = a.idpago
inner join
    tfacturas c
on
    c.idfactura = a.idfactura
where
    b.timbrado is not null and
    c.timbrado is not null and
    b.timbrado < date_sub(c.timbrado, interval 24 hour)
order by
    a.idfactura,
    a.idpagofactura";
$result = mysqli_query($con, $query);

if(!$result){
    exit("Error al consultar las amortizaciones: ".mysqli_error($con)."\n");
}

$anomalas = mysqli_fetch_all($result, MYSQLI_ASSOC);

if(count($anomalas)==0){
    exit("No se encontraron amortizaciones mal atribuidas.\n");
}

// Se agrupan por factura porque la verificación de saldo es a nivel factura
$porfactura = array();

foreach($anomalas as $fila){
    $porfactura[$fila["idfactura"]][] = $fila;
}

$borradas = 0;
$saltadas = 0;
$errores = 0;

foreach($porfactura as $idfactura => $filas){
    $factura = $filas[0];
    $etiqueta = "Factura #".$idfactura." ".$factura["serie"]."-".$factura["folio"];

    // Suma de lo que SÍ amortizaron los complementos legítimos de esta factura (los que no
    // están en esta lista y no son pagos cancelados, que ya devolvieron su monto al saldo)
    $ids = array();
    foreach($filas as $fila){
        $ids[] = (int)$fila["idpagofactura"];
    }

    $query = "
    select
        coalesce(sum(a.monto),0) as amortizado
    from
        tpagosfacturas a
    inner join
        tpagos b
    on
        b.idpago = a.idpago
    where
        a.idfactura = '".(int)$idfactura."' and
        a.idpagofactura not in (".implode(",", $ids).") and
        b.status <> 3";
    $amortizado = (float)mysqli_fetch_assoc(mysqli_query($con, $query))["amortizado"];

    $total = (float)$factura["total"];
    $saldo = (float)$factura["saldo"];
    $saldoesperado = round($total - $amortizado, 2);

    echo $etiqueta." | total $".number_format($total,2)." | saldo $".number_format($saldo,2)."\n";

    foreach($filas as $fila){
        echo "    #".$fila["idpagofactura"]." pago ".$fila["serie_pago"]."-".$fila["folio_pago"]
            ." (".$fila["uuid_pago"].") status ".$fila["status_pago"]
            ." | monto $".number_format($fila["monto"],2)
            ." | timbrado pago ".$fila["timbrado_pago"]." < factura ".$fila["timbrado_factura"]."\n";
    }

    // El borrado solo es seguro si el saldo actual ya corresponde a la factura SIN estas
    // amortizaciones; si no, el saldo sí se descontó con ellas y hay que revisarlo a mano
    if(abs($saldo - $saldoesperado) > 0.02){
        echo "    [SALTADA] el saldo ($".number_format($saldo,2).") no coincide con el esperado sin estas amortizaciones ($".number_format($saldoesperado,2)."). Revisar a mano.\n\n";
        $saltadas += count($filas);
        continue;
    }

    if(!$aplicar){
        echo "    [PENDIENTE] se borrarían ".count($filas)." fila(s)\n\n";
        $borradas += count($filas);
        continue;
    }

    $query = "
    delete
    from
        tpagosfacturas
    where
        idpagofactura in (".implode(",", $ids).")";

    if(mysqli_query($con, $query)){
        echo "    [BORRADA] ".mysqli_affected_rows($con)." fila(s)\n\n";
        $borradas += mysqli_affected_rows($con);
    }else{
        echo "    [ERROR SQL] ".mysqli_error($con)."\n\n";
        $errores += count($filas);
    }
}

echo "Filas ".($aplicar ? "borradas" : "por borrar").": ".$borradas."\n";
echo "Saltadas:                 ".$saltadas."\n";
echo "Errores:                  ".$errores."\n";

if(!$aplicar){
    echo "\nNo se borró nada. Vuelve a correr con --aplicar para aplicar los cambios.\n";
}
