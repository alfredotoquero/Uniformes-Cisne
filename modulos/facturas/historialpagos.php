<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pagos.php");

$claseFacturas = new Facturas();
$clasePagos = new Pagos();

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

$factura = $claseFacturas->getFactura(array("idfactura" => $idfactura))["factura"];
$pagos = $clasePagos->getPagosFactura(array("idfactura" => $idfactura))["pagos"];

// Todas las amortizaciones cuentan: la fila se borra cuando el pago se revierte, así que
// las que siguen aquí son las que están descontadas del saldo de la factura
$totalaplicado = 0;
foreach($pagos as $pago){
    $totalaplicado += $pago["monto"];
}
?>
<div style="width:900px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Pagos de la factura <?= $factura["serie"]."-".$factura["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <?php if(count($pagos)==0){ ?>
        <div class="alert alert-info mb-0">Esta factura no tiene pagos aplicados.</div>
    <?php }else{ ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Complemento</th>
                        <th>UUID</th>
                        <th>Forma de pago</th>
                        <th class="text-center">Parcialidad</th>
                        <th class="text-end">Total del pago</th>
                        <th class="text-end">Aplicado a esta factura</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pagos as $pago){ ?>
                        <tr>
                            <td><?= ($pago["fecha"]) ? date("d/m/Y",strtotime($pago["fecha"])) : "—"; ?></td>
                            <td><?= ($pago["serie"] || $pago["folio"]) ? $pago["serie"]."-".$pago["folio"] : "—"; ?></td>
                            <td><small><?= $pago["uuid"] ? $pago["uuid"] : "—"; ?></small></td>
                            <td><?= $pago["formapago"] ? $pago["formapago"]." - ".$pago["descripcion_formapago"] : "—"; ?></td>
                            <td class="text-center"><?= $pago["parcialidad"]; ?></td>
                            <td class="text-end">$<?= number_format($pago["total"],2); ?></td>
                            <td class="text-end">$<?= number_format($pago["monto"],2); ?></td>
                            <td class="text-end">
                                <?php if(!empty($pago["uuid"])){ ?>
                                    <a href="javascript:;" onclick="solicitudServidor('pagos','verPDF','idpago=<?= $pago['idpago'] ?>','');" class="btn btn-secondary btn-sm" title="Ver PDF"><i class="uil uil-file-alt"></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total aplicado</th>
                        <th class="text-end">$<?= number_format($totalaplicado,2); ?></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-end">Saldo de la factura</th>
                        <th class="text-end">$<?= number_format($factura["saldo"],2); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php } ?>
</div>
