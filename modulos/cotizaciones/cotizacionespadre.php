<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$claseCotizaciones = new Cotizaciones();
$_POST["idcotizacion"] = $_GET["modulo3"];
$cotizacion = $claseCotizaciones->obtenerCotizacionMasReciente($_POST);

// echo "c: " . $cotizacion["cotizacion"]["idcotizacion"];

$convertida = $claseCotizaciones->convertidaAPedido($_POST);

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="/cotizaciones" class="btn btn-danger btn-sm"><i class="uil uil-history-alt me-1"></i>Regresar</a>
                </div>
                <h4 class="page-title">Historial de Cotizaciones</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded">
                        <!-- <form id="formBusqueda" name="formBusqueda">
                            <input type="hidden" name="archivo" id="archivo" value="/modulos/cotizaciones/cotizacionespadre/lista.php">
                            <input type="hidden" name="contenedor" id="contenedor" value="divLista">
                            <input type="hidden" name="idcotizacion" id="idcotizacion" value="<?= $_GET["modulo3"] ?>">
                        </form> -->
                    </div>
                    <hr>
                    <div id="divLista">
                        <?
                        // if(false){
                        if($cotizacion["respuesta"]=="OK"){
                            $cotizacion = $cotizacion["cotizacion"];
                            ?>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Cliente</th>
                                            <th>Usuario</th>
                                            <th>Subtotal</th>
                                            <th>IVA</th>
                                            <th>Total</th>
                                            <th>Fecha</th>
                                            <th style="width: 200px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        $_POST["idcotizacion"] = $cotizacion["idcotizacion"];
                                        while($claseCotizaciones->obtenerCotizacion($_POST)["respuesta"]=="OK"){
                                            // $_POST["idcotizacion"] = 
                                            $cotizacion = $claseCotizaciones->obtenerCotizacion($_POST)["cotizacion"];
                                            ?>
                                            <tr>
                                                <td><?= $cotizacion["idcotizacion"]; ?></td>
                                                <td><?= $cotizacion["cliente"]; ?></td>
                                                <td><?= $cotizacion["usuario"]; ?></td>
                                                <td>$<?= number_format($cotizacion["subtotal"],2); ?></td>
                                                <td>$<?= number_format($cotizacion["iva"],2); ?></td>
                                                <td>$<?= number_format($cotizacion["total"],2); ?></td>
                                                <td><?= fecha_formateada($cotizacion["fecha"]); ?></td>
                                                <td>
                                                    <a href="/modulos/cotizaciones/cotizacion.php?idcotizacion=<?= $cotizacion["idcotizacion"]; ?>" class="btn btn-info btn-sm mb-1" title="Ver cotización" target="_blank"><i class="uil uil-eye"></i></a>
                                                    <?
                                                    // modificar la cotizacion o convertirla a pedido solo se puede hacer si no se ha convertido a pedido aún
                                                    $_POST["idcotizacion"] = $cotizacion["idcotizacion"];
                                                    if (!$convertida) {
                                                        ?>
                                                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/cotizaciones/convertir.php?idcotizacion=<?= $cotizacion["idcotizacion"]; ?>" class="btn btn-warning btn-sm mb-1" title="Convertir a Pedido"><i class="uil-file-check"></i></a>
                                                        <?
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?
                                            $_POST["idcotizacion"] = $cotizacion["idcotizacionpadre"];
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                        }else{
                            ?>
                            <div class="card text-white bg-danger">
                                <div class="card-body p-3">
                                    <?= $cotizaciones["mensaje"] ?>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
