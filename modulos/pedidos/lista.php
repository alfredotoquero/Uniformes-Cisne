<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$clasePedidos = new Pedidos();
$pedidos = $clasePedidos->obtenerPedidos($_POST);

$claseUsuarios = new Usuarios();

$idusuario = $_SESSION["usuario"]["idusuario"];
$facturarPedidos = $claseUsuarios->verificarPermiso($idusuario, 1)["respuesta"] == "OK";

if ($pedidos["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="40">#</th>
                    <th>Cliente</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th>Abonado</th>
                    <th>Restante</th>
                    <th>Fecha</th>
                    <th>Factura</th>
                    <?
                    if ($_POST["tipopedido"] == "finalizados") {
                    ?>
                        <td width="230">Usuario Finalización</td>
                    <?
                    }
                    ?>
                    <th style="width: 350px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach ($pedidos["pedidos"] as $pedido) {
                ?>
                    <tr>
                        <td><?= $pedido["idpedido"]; ?></td>
                        <td><?= $pedido["cliente"]; ?></td>
                        <td><?= $pedido["usuario"]; ?></td>
                        <td>$<?= number_format($pedido["total"], 2); ?></td>
                        <td>$<?= number_format($pedido["abonado"], 2); ?></td>
                        <td>$<?= number_format($pedido["total"] - $pedido["abonado"], 2); ?></td>
                        <td><?= fecha_formateada($pedido["fecha"]); ?></td>
                        <td><?= (!empty($pedido["idfactura"]) ? $pedido["factura_serie"] . "-" . $pedido["factura_folio"] : "-"); ?></td>
                        <?
                        if ($_POST["tipopedido"] == "finalizados") {
                        ?>
                            <td><? echo (($pedido["idusuariofinalizacion"] != 0 and $pedido["idusuariofinalizacion"] != "") ? $pedido["usuariofinalizacion"] : "-") . (($pedido["idusuarioentrega"] != 0 and $pedido["idusuarioentrega"] != "") ? "<br>Entregado: " . $pedido["usuarioentrega"] : "") . (($pedido["status"] == "C") ? "<br>CANCELADO" : "") . (($pedido["fechaactualizacion"] != "0000-00-00 00:00:00" and $pedido["fechaactualizacion"] != null) ? "<br>" . fecha_formateada($pedido["fechaactualizacion"]) : ""); ?></td>
                        <?
                        }
                        ?>
                        <!--

                    opciones de pedido:
                    informacion
                    editar
                    activar
                    entregar
                    pedido
                    produccion
                    cotizacion
                    finalizar
                    cancelar

                    -->
                        <td class="text-end">
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/informacion.php?idpedido=<?= $pedido["idpedido"] ?>" class="btn btn-info btn-sm mb-1" title="Informacion"><i class="uil uil-info-circle"></i></a>
                            <?
                            if(empty($pedido["idfactura"]) && $facturarPedidos){
                            ?>
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/facturar.php?idpedido=<?= $pedido["idpedido"] ?>" class="btn btn-info btn-sm mb-1" title="Facturar"><i class="uil uil-file-alt"></i></a>
                            <?
                            }
                            $_POST["idpedido"] = $pedido["idpedido"];
                            if ($claseUsuarios->esAdministrador($idusuario)) {
                            ?>
                                <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/editarsucursal.php?idpedido=<?= $pedido["idpedido"] ?>&idsucursal=<?= $pedido["idsucursal"] ?>" class="btn btn-primary btn-sm mb-1" title="Cambiar sucursal"><i class="mdi mdi-warehouse"></i></a>
                            <?
                            }
                            // si un pedido está pagado, o si ha sido entregado o cancelado, no se puede editar
                            // si se autorizó un movimiento asociado, o si se inició producción, no se puede editar
                            if ($pedido["statuspago"] != 1 && $pedido["abonado"] == 0 && ($pedido["status"] != "E" && $pedido["status"] != "C") && $pedido["statusproduccion"] == "S" && !$clasePedidos->alMenosUnMovimientoAutorizado($_POST)["respuesta"]) {
                            ?>
                                <a href="/pedidos/editar/<?= $pedido["idpedido"] ?>" class="btn btn-primary btn-sm mb-1" title="Editar"><i class="uil uil-edit"></i></a>
                            <?
                            }
                            if ($pedido["pendiente"] == 0 and $pedido["status"] != "E" and $pedido["status"] != "C") {
                                $datos = array("idpedido" => $pedido["idpedido"]);
                                $datos = json_encode($datos);
                            ?>
                                <a href="javascript:;" onclick='solicitarPassword("activar","ajax",<?= $datos ?>,"administrativa");' class="btn btn-success btn-sm mb-1" title="Activar"><i class="uil uil-file-check"></i></a>
                            <?
                            }
                            if ($pedido["status"] != "E" and $pedido["status"] != "C" and $pedido["statuspago"] == 1) {
                                $datos = array("idpedido" => $pedido["idpedido"]);
                                $datos = json_encode($datos);
                            ?>
                                <a href="javascript:;" onclick='solicitarPassword("entregar","fancy",<?= $datos ?>,"","/modulos/pedidos/entregarproductos.php")' class="btn btn-success btn-sm mb-1" title="Entregar"><i class="uil uil-package"></i></a>
                            <?
                            }
                            if ($pedido["status"] == "A" && $claseUsuarios->esAdministrador($idusuario)) {
                                $datos = array("idpedido" => $pedido["idpedido"], "motivo" => "", "status" => "", "accionmovimientos" => "");
                                $datos = json_encode($datos);
                            ?>
                                <!-- <a href="javascript:;" onclick="solicitudServidor('pedidos','finalizar','idpedido=<?= $pedido['idpedido'] ?>','¿Deseas finalizar el pedido?','');" class="btn btn-warning btn-sm mb-1" title="Finalizar"><i class="uil uil-check"></i></a> -->
                                <a href="javascript:;" onclick='finalizar(<?= $datos ?>);' class="btn btn-warning btn-sm mb-1" title="Finalizar"><i class="uil uil-check"></i></a>
                            <?
                            }
                            ?>
                            <!-- <div class="dropdown"> -->
                            <button class="btn btn-secondary btn-sm mb-1" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-file-pdf-box"></i> PDF <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                <li><a href="/modulos/pedidos/pedido.php?idpedido=<?= $pedido["idpedido"] ?>" class="dropdown-item" target="_blank">Pedido</a></li>
                                <li><a href="/modulos/pedidos/produccion.php?idpedido=<?= $pedido["idpedido"] ?>" class="dropdown-item" target="_blank">Producción</a></li>
                                <?
                                if ($pedido["idcotizacion"] > 0) {
                                ?>
                                    <li><a href="/modulos/cotizaciones/cotizacion.php?idcotizacion=<?= $pedido["idcotizacion"] ?>" class="dropdown-item" target="_blank">Cotización</a></li>
                                <?
                                }
                                if (!empty($pedido["idfactura"])) {
                                ?>
                                    <li><a href="javascript:;" onclick="solicitudServidor('facturas','verPDF','idfactura=<?= $pedido['idfactura'] ?>','');" class="dropdown-item">Factura</a></li>
                                <?
                                }
                                ?>
                            </ul>
                            <!-- </div> -->
                            <?
                            if ($pedido["status"] != "C" and $pedido["status"] != "E" and $pedido["statuspago"] == 0 and $pedido["statusproduccion"] == "") {
                            ?>
                                <a href="javascript:;" onclick="solicitudServidor('pedidos','cancelar','idpedido=<?= $pedido['idpedido'] ?>','¿Deseas cancelar el pedido?','');" class="btn btn-danger btn-sm mb-1" title="Cancelar"><i class="uil uil-times"></i></a>
                            <?
                            }
                            ?>

                        </td>
                    </tr>
                <?
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-end">
            <nav>
                <ul class="pagination">
                    <? if ($pedidos["pagina"] > 2) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                    <? } ?>
                    <? if ($pedidos["pagina"] > 1) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['pagina'] - 1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                    <? } ?>
                    <?
                    $i = 0;
                    while ($i < $pedidos["maxpaginas"]) {
                        $page = $pedidos["pagina"] - ($pedidos["maxpaginas"] - $i);
                        if ($page > 0) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i++;
                    }
                    ?>
                    <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['pagina']; ?>);" href="javascript:;"><? echo $pedidos["pagina"]; ?></a></li>
                    <?
                    $i = $pedidos["maxpaginas"];
                    while ($i > 0) {
                        $page = $pedidos["pagina"] + ($pedidos["maxpaginas"] - ($i - 1));
                        if ($page <= $pedidos["numpaginas"]) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i--;
                    }
                    ?>
                    <? if ($pedidos["pagina"] < $pedidos["numpaginas"]) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($pedidos['pagina']) + 1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                    <? } ?>
                    <? if ($pedidos["pagina"] < ($pedidos["numpaginas"] - 1)) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pedidos['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
                    <? } ?>
                </ul>
            </nav>
        </div>
    </div>
<?
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $pedidos["mensaje"] ?>
        </div>
    </div>
<?
}
?>