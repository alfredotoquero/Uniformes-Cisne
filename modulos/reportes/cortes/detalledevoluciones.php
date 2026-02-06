<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();

$_POST["idcorte"] = $_GET["idcorte"];
$corte = $claseReportes->obtenerCorte($_POST)["corte"];
$tickets = $claseReportes->obtenerDetalleDevolucionesCorte($_POST);


?>

<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Detalle de Devoluciones</h4>
        </div>
    </div>
    <hr>

    <div id="divReporte" class="box">
        <div class="box-header">
            <div class="row">
                <div class="col-8">
                    <?= "<b># Corte:</b> " . $corte["folio"] . "<br><b>Sucursal:</b> " . $corte["sucursal"]."<br><b>Vendedor:</b> " . $corte["vendedor"] . "<br><b>Fecha:</b> " . fecha_formateada_largo($corte["fechainicial"]) . "<br><b>Status:</b> " . ($corte["status"]=="A" ? "Activo" : ($corte["status"]=="T" ? "Terminado" : "")) . "</b>"; ?>
                </div>
                <div class="col-4 text-right no-print" style="text-align:right !important;">
                    <button type="button" class="btn btn-primary waves-effect waves-light mr-2" name="btnImprimir" id="btnImprimir" onclick="$('#divReporte').print();">Imprimir</button>
                </div>
            </div>
        </div>

	
        <div class="box-body" id="listaCuentas">
            <div class="table-responsive">
                <?
                // se deben mostrar todas las cosas con respecto a los gastos (por lo pronto, sólo devoluciones)
                // devoluciones hechas durante este corte
                if ($tickets["respuesta"]=="OK") {
                    ?>
                    <table class="table table-striped b-t">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th style="width:150px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach($tickets["tickets"] as $ticket) {
                                ?>
                                <tr>
                                    <td><?= $ticket["folio"]; ?></td>
                                    <td><?= $ticket["vendedor"]; ?></td>
                                    <td>$<?= number_format($ticket["totaldevolucion"],2); ?></td>
                                    <td><?= fecha_formateada($ticket["fecha"]); ?></td>
                                    <td>
                                        <a href="javascript:;" data-fancybox data-src="/modulos/reportes/cortes/detalleticketgastos.php?idcuenta=<?= $ticket["idcuenta"]; ?>&idcorte=<?= $_GET["idcorte"]; ?>&idticket=<?= $ticket["idticket"]; ?>" data-options='{"type" : "ajax", "closeExisting": true, "clickSlide": false}' class="btn btn-secondary">Ver detalle</a>
                                    </td>
                                </tr>
                                <?
                            }
                            ?>
                        </tbody>
                    </table>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</div>

