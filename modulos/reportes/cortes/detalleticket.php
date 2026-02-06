<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();

$_POST["idticket"] = $_GET["idticket"];
$ventas = $claseReportes->obtenerVentas($_POST);
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Detalle del Ticket</h4>
        </div>
    </div>
    <hr>
    <?
    if ($ventas["respuesta"] == "OK") {
        $ventas = $ventas["ventas"];
        $ticket = mysqli_fetch_array($ventas);
    ?>
        <div class="padding">
            <div class="box">
                <div class="box-header">
                    <div class="row">
                        <div class="col">
                            <?= "<b># Ticket:</b> " . $ticket["folio"] . "<br><b>Vendedor:</b> " . $ticket["vendedor"] . "<br><b>Sucursal:</b> " . $ticket["sucursal"] . "<br><b>Fecha:</b> " . fecha_formateada_largo($ticket["fecha"]) . "</b>";
                            ?>
                        </div>

                        <div class="col text-right" style="text-align:right;">
                            <a href="javascript:;" data-fancybox data-src="/modulos/reportes/cortes/detalleventas.php?idcorte=<?= $_GET["idcorte"]; ?>" data-options='{"type" : "ajax", "closeExisting": true, "clickSlide": false}' class="btn btn-dark waves-effect waves-light">Regresar</a>
                        </div>
                    </div>
                </div>

                <div class="box-body">
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
                                $subtotal = 0;
                                $detalle = "";
                                // if ($ticket["productos"]!="") {
                                $cantidades = explode(",", $ticket["cantidades"]);
                                $productos = explode("-_-", $ticket["productos"]);
                                $colores = explode("-_-", $ticket["colores"]);
                                $tallas = explode("-_-", $ticket["tallas"]);
                                $subtotales = explode(",", $ticket["subtotales"]);
                                $ivas = explode(",", $ticket["ivas"]);
                                $totales = explode(",", $ticket["totales"]);
                                $descuentos = explode(",", $ticket["descuentos"]);
                                foreach ($productos as $i => $producto) {
                                    $cantidad = $cantidades[$i];
                                    $total = $totales[$i];
                                    $descuento = $descuentos[$i];
                                    $precio = $total / $cantidad;
                                    $subtotalp = $subtotales[$i];
                                    $ivap = $ivas[$i];
                                    $totalp = $subtotalp + $ivap;

                                    $talla = $tallas[$i];
                                    $color = $colores[$i];
                                    // }

                                    // se agrega costo adicional por cada personalizacion seleccionada

                                    $subtotal += (float)($cantidad * ($precio * ((100 - $descuento) / 100) / 1.08));
                                ?>
                                    <tr>
                                        <td align="center"><?= $cantidad; ?></td>
                                        <td>
                                            <?
                                            echo $producto;

                                            // personalizaciones
                                            // $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                                            // while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                                            // $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                                            // echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
                                            // }
                                            ?>
                                        </td>
                                        <td><?= $talla; ?></td>
                                        <td><?= $color; ?></td>
                                        <td>$<?= number_format($precio, 2); ?></td>

                                        <td>$<?= number_format($precio * ($descuento / 100), 2) ?></td>
                                        <td>$<?= number_format($totalp, 2); ?></td>

                                    </tr>
                                <?
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <?
                    $pagos = explode("-", $ticket["formaspago"]);
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-md-4 offset-md-8">
                            <div class="table-responsive">
                                <?
                                if (count($pagos)) {
                                ?>
                                    <table class="table m-0 table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Forma de Pago</th>
                                                <th>Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?
                                            foreach ($pagos as $formapago) {
                                                $datos = explode(":", $formapago);
                                                $idformapago = $datos[0];
                                                $monto = $datos[1];
                                                $nombre = $datos[2];

                                            ?>
                                                <tr>
                                                    <td><?= $nombre; ?></td>
                                                    <td>$<?= number_format($monto, 2); ?></td>
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

                    <?
                    $_POST["idcuenta"] = $_GET["idcuenta"];
                    $_POST["idcorte"] = $_GET["idcorte"];
                    $ticket = $claseReportes->obtenerDetalleDevolucionesTicket($_POST);
                    if ($ticket["respuesta"] == "OK") {
                        $ticket = $ticket["ticket"];
                    ?>
                        <b>DEVOLUCIONES</b><br><br>
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                    <tr>
                                        <th width="30">Cant.</th>
                                        <th>Producto</th>
                                        <th width="100">P.U.</th>
                                        <th width="100">Descuento</th>
                                        <th width="100">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    $partidas = explode(";", $ticket["productoscuenta"]);
                                    foreach ($partidas as $partida) {
                                        $partida = explode("-_-", $partida);

                                        $precio = $partida[2];
                                        $cantidad = $partida[0];
                                        $descuento = $partida[3];
                                        $subtotalp = $partida[5];
                                        $ivap = $partida[6];
                                        $totalp = $partida[4];

                                        $datos = explode(",", $partida[1]);
                                        $producto = $datos[0];
                                    ?>
                                        <tr>
                                            <td align="center"><?= $cantidad; ?></td>
                                            <td><?= $producto; ?></td>
                                            <td>$<?= number_format($precio, 2); ?></td>
                                            <td>$<?= number_format($precio * ($descuento / 100), 2) ?></td>
                                            <td>$<?= number_format($totalp, 2); ?></td>

                                        </tr>
                                    <?
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?
                    }
                    ?>
                </div>

            </div>
        </div>

        <div class="padding">
            <div class="box">
                <div class="box-header">
                    <div class="p-2">
                        <div class="row">
                            <div class="col">
                                <center>
                                    <h2>Historial de Pagos</h2>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <?
                        $pagos = $claseReportes->obtenerHistorialPagosTicket($_POST);

                        if ($pagos["respuesta"] == "OK") {
                        ?>
                            <table class="table table-striped b-t">
                                <thead>
                                    <tr>
                                        <th># Ticket</th>
                                        <th>Sucursal</th>
                                        <th>Vendedor</th>
                                        <th>Notas</th>
                                        <th width="100">Abono</th>
                                        <th width="200">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    $abonado = 0;
                                    foreach ($pagos["pagos"] as $pago) {
                                    ?>
                                        <tr>
                                            <td><?= $pago["folio"]; ?></td>
                                            <td><?= $pago["sucursal"]; ?></td>
                                            <td><?= $pago["vendedor"]; ?></td>
                                            <td><?= $pago["notas"]; ?></td>
                                            <td>$<?= number_format($pago["total"], 2); ?></td>
                                            <td><?= fecha_formateada($pago["fecha"]); ?></td>
                                        </tr>
                                    <?
                                        $abonado += $pago["total"];
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><b>Saldo</b></td>
                                        <?
                                        $total = $pagos["totalcuenta"];
                                        $saldo = $total - $abonado;
                                        ?>
                                        <td>$<? echo number_format($saldo, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?
    }
    ?>
</div>