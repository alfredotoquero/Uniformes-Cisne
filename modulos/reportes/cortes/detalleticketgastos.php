<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();

$_POST["idcorte"] = $_GET["idcorte"];
$_POST["idcuenta"] = $_GET["idcuenta"];
$ticket = $claseReportes->obtenerDetalleDevolucionesTicket($_POST);

?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Productos devueltos del Ticket</h4>
        </div>
    </div>
    <hr>

<div class="padding">
	
	<div class="box">
        <?
        if ($ticket["respuesta"]=="OK") {
            $ticket = $ticket["ticket"];
            ?>
            <div class="box-header">
                <div class="row">
                    <div class="col">
                        <?= "<b># Ticket:</b> " . $ticket["folio"] . "<br><b>Vendedor:</b> " . $ticket["vendedor"] . "<br><b>Sucursal:</b> " . $ticket["sucursal"] . "<br><b>Fecha:</b> " . fecha_formateada_largo($ticket["fecha"]) . "</b>";  ?>
                    </div>

                    <div class="col text-right" style="text-align:right;">
                        <a href="javascript:;" data-fancybox data-src="/modulos/reportes/cortes/detalledevoluciones.php?idcorte=<?= $_GET["idcorte"]; ?>" data-options='{"type" : "ajax", "closeExisting": true, "clickSlide": false}' class="btn btn-dark waves-effect waves-light">Regresar</a>
                    </div>
                </div>
            </div>

            <div class="box-body" >
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th width="30">Cant.</th>
                                <th>Producto</th>
                                <th width="200">Talla</th>
                                <th width="200">Color</th>
                                <th width="100">P.U.</th>
                                <th width="100">Descuento</th>
                                <th width="100">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            $partidas = explode(";",$ticket["productoscuenta"]);
                            foreach($partidas as $partida){
                                $datos = explode("-_-",$partida);
                                $cantidad = $datos[0];
                                $precio = $datos[2];
                                $descuento = $datos[3];
                                $total = $datos[4];
                                $producto = explode(",",$datos[1]);
                                $nombre = $producto[0];
                                $talla = $producto[1];
                                $color = $producto[2];

                            ?>
                            <tr>
                                <td align="center"><? echo $partida[0]; ?></td>
                                <td><? echo $nombre; ?></td>
                                <td><? echo $talla; ?></td>
                                <td><? echo $color; ?></td>
                                <td>$<? echo number_format($precio,2); ?></td>
                                <td>$<? echo number_format($precio*($descuento/100),2); ?></td>
                                <td>$<? echo number_format($total,2); ?></td>
                            </tr>
                            <?
                            }
                            $iva = ($subtotal) * 0.08;
                            $total = (float)($subtotal) + (float)$iva;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?
        }else {
            echo $ticket["mensaje"];
        }
        ?>
	</div>
</div>

</div>
