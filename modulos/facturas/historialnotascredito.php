<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/NotasCredito.php");

$claseFacturas = new Facturas();
$claseNotasCredito = new NotasCredito();

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

$factura = $claseFacturas->getFactura(array("idfactura" => $idfactura))["factura"];
$notascredito = $claseNotasCredito->getNotasCredito(array("idfactura" => $idfactura))["notascredito"];

$tiposrelacion = $claseNotasCredito->obtenerTiposRelacion();
?>
<div style="width:900px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Notas de crédito de la factura <?= $factura["serie"]."-".$factura["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <?php if(count($notascredito)==0){ ?>
        <div class="alert alert-info mb-0">Esta factura no tiene notas de crédito.</div>
    <?php }else{ ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Nota</th>
                        <th>Tipo de relación</th>
                        <th>Descripción</th>
                        <th>UUID</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">IVA</th>
                        <th class="text-end">Total</th>
                        <th style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($notascredito as $nota){ ?>
                        <tr>
                            <td><?= str_replace("<br>"," ",fecha_formateada($nota["registro"])); ?></td>
                            <td><?= $nota["serie"]."-".$nota["folio"]; ?></td>
                            <td><?= $nota["tiporelacion"]." - ".(isset($tiposrelacion[$nota["tiporelacion"]]) ? $tiposrelacion[$nota["tiporelacion"]] : ""); ?></td>
                            <td><?= htmlspecialchars($nota["descripcion"], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><small><?= $nota["uuid"]; ?></small></td>
                            <td class="text-end">$<?= number_format($nota["subtotal"],2); ?></td>
                            <td class="text-end">$<?= number_format($nota["iva"],2); ?></td>
                            <td class="text-end">$<?= number_format($nota["total"],2); ?></td>
                            <td class="text-end">
                                <a href="javascript:;" onclick="solicitudServidor('notascredito','verPDF','idnotacredito=<?= $nota['idnotacredito'] ?>','');" class="btn btn-secondary btn-sm" title="Ver PDF"><i class="uil uil-file-alt"></i></a>
                                <a href="/modulos/facturas/descargarnotacredito.php?idnotacredito=<?= $nota['idnotacredito'] ?>" class="btn btn-secondary btn-sm" title="Descargar archivos"><i class="uil uil-download-alt"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
