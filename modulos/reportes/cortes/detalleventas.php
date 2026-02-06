<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();

$_POST["idcorte"] = $_GET["idcorte"];
$corte = $claseReportes->obtenerCorte($_POST)["corte"];
$ventas = $claseReportes->obtenerVentas($_POST);

?>

<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Detalle de Ventas</h4>
        </div>
    </div>
    <hr>

    <div class="padding">
        <div id="divReporte" class="box">
            <?
            if ($ventas["respuesta"]=="OK") {
                ?>
                <div class="box-header">
                    <div class="row">
                        <div class="col">
                            <?= "<b># Corte:</b> " . $corte["folio"] . "<br><b>Sucursal:</b> " . $corte["sucursal"]."<br><b>Vendedor:</b> " . $corte["vendedor"] . "<br><b>Fecha:</b> " . fecha_formateada_largo($corte["fechainicial"]) . "<br><b>Status:</b> " . ($corte["status"]=="A" ? "Activo" : ($corte["status"]=="T" ? "Terminado" : "")) . "</b>"; ?>
                        </div>
                        <div class="col text-right no-print" style="text-align:right;">
                            <button type="button" class="btn btn-primary waves-effect waves-light mr-2" name="btnImprimir" id="btnImprimir" onclick="$('#divReporte').print();">Imprimir</button>
                        </div>
                    </div>
                </div>
        
                <div class="box-body" id="listaCuentas">
                    <div class="table-responsive">
                        <table class="table table-striped b-t">
                            <thead>
                                <tr>
                                    <th># Ticket</th>
                                    <th>Tipo</th>
                                    <th>Detalle</th>
                                    <th>Vendedor</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                foreach ($ventas["ventas"] as $ticket) {
                                    // desglose 
                                    ?>
                                    <tr id="<? echo $ticket["idticket"]; ?>">
                                        <td><?= $ticket["folio"] ?></td>
                                        <td><?= ($ticket["tipocuenta"]=="A" ? "Apartado" : (($ticket["tipocuenta"]=="") ? "Pedido #".$ticket["idpedido"] : "Venta")); ?></td>
                                        <?
                                        $detalle = "";
                                        if ($ticket["productos"]!="") {
                                            $cantidades = explode(",",$ticket["cantidades"]);
                                            $productos = explode("-_-",$ticket["productos"]);
                                            $colores = explode("-_-",$ticket["colores"]);
                                            $tallas = explode("-_-",$ticket["tallas"]);
                                            foreach ($productos as $i => $producto) {
                                                $detalle .= $cantidades[$i] . " " . $producto . " | Talla: " . $tallas[$i] . " Color: " . $colores[$i] . "<br>";
                                            }
                                        }
                                        ?>
                                        <td><?= $detalle ?></td>
                                        <td><?= $ticket["vendedor"] ?></td>
                                        <?
                                        $detalle = "$" . number_format($ticket["total"],2);
                                        if ($ticket["formaspago"]!="") {
                                            $formaspago = explode("-",$ticket["formaspago"]);
                                            foreach ($formaspago as $formapago) {
                                                $datos = explode(":",$formapago);
                                                $idformapago = $datos[0];
                                                $monto = $datos[1];
                                                $nombre = $datos[2];

                                                $detalle .= "<br>" . $nombre . ": $" . number_format($monto,2);
                                            }
                                            
                                        }
                                        ?>
                                        <td><?= $detalle; ?></td>
                                        <td><?= fecha_formateada($ticket["fecha"]); ?></td>
                                        <td>
                                            <? if($ticket["tipocuenta"]!=""){ ?>
                                            <a href="javascript:;" class="btn btn-secondary" data-fancybox data-src="/modulos/reportes/cortes/detalleticket.php?idticket=<? echo $ticket["idticket"]; ?>&idcuenta=<? echo $ticket["idcuenta"]; ?>&idcorte=<? echo $_GET["idcorte"]; ?>" data-options='{"type" : "ajax", "closeExisting": true, "clickSlide": false}'>Ver detalle</a>
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </tbody>
                        </table>
                        <?
                    }else{
                        echo $ventas["mensaje"];
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
