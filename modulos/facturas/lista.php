<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Facturas.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$claseFacturas = new Facturas();
$facturas = $claseFacturas->getFacturas($_POST);

$claseUsuarios = new Usuarios();

$idusuario = $_SESSION["usuario"]["idusuario"];

if ($facturas["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="40">#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Saldo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-center">Pagos</th>
                    <th class="text-center">Nota de crédito</th>
                    <th>Pedido</th>
                    <th>Ticket</th>
                    <th style="width: 160px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach ($facturas["facturas"] as $factura) {
                ?>
                    <tr>
                        <td><?= $factura["serie"]." - ".$factura["folio"]; ?></td>
                        <td><?= $factura["cliente"]; ?></td>
                        <td>$<?= number_format($factura["total"],2); ?></td>
                        <td>$<?= number_format($factura["saldo"],2); ?></td>
                        <td><?= str_replace("<br>"," ",fecha_formateada($factura["registro"])); ?></td>
                        <td>
                            <?php
                            switch((int)$factura["status"]){
                                case 1:  echo '<span class="badge bg-success">ACTIVA</span>'; break;
                                case 2:  echo '<span class="badge bg-warning text-dark">PROCESO DE CANCELACIÓN</span>'; break;
                                default: echo '<span class="badge bg-danger">CANCELADA</span>'; break;
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <?= ($factura["pagos"] > 0) ? '<i class="uil uil-check text-success"></i>' : '<i class="uil uil-times text-danger"></i>'; ?>
                        </td>
                        <td class="text-center">
                            <?= ($factura["notascredito"] > 0) ? '<i class="uil uil-check text-success"></i>' : '<i class="uil uil-times text-danger"></i>'; ?>
                        </td>
                        <td><?= $factura["idpedido"] ? $factura["idpedido"] : "—"; ?></td>
                        <td><?= $factura["folio_ticket"] ? $factura["folio_ticket"] : "—"; ?></td>
                        <td class="text-end">
                            <button class="btn btn-secondary btn-sm mb-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Opciones <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:;" onclick="solicitudServidor('facturas','verPDF','idfactura=<?= $factura['idfactura'] ?>','');" class="dropdown-item">Ver Factura</a></li>
                                <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/reenviar.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Reenviar Factura</a></li>
                                <li><a href="/modulos/facturas/descargar.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Descargar Archivos</a></li>
                                <?
                                if ($factura["idpedido"]) {
                                ?>
                                    <li><a href="/modulos/pedidos/pedido.php?idpedido=<?= $factura['idpedido'] ?>" class="dropdown-item" target="_blank">PDF Pedido</a></li>
                                    <li><a href="/modulos/pedidos/produccion.php?idpedido=<?= $factura['idpedido'] ?>" class="dropdown-item" target="_blank">PDF Producción</a></li>
                                <?
                                }
                                if ($factura["status"] == 1) {
                                ?>
                                    <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/refacturar.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Refacturar</a></li>
                                    <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/cancelar.php?idfactura=<?= $factura["idfactura"] ?>" class="dropdown-item">Cancelar Factura</a></li>
                                    <?
                                    // La nota de crédito solo tiene sentido mientras quede saldo por
                                    // acreditar: una factura saldada ya no puede recibir más egresos
                                    if ($factura["saldo"] > 0) {
                                    ?>
                                        <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/notacredito.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Generar Nota de Crédito</a></li>
                                    <?
                                    }
                                }
                                // La cancelación quedó pendiente de que el receptor la acepte
                                // o la rechace ante el SAT: desde aquí se reconsulta y se
                                // resuelve sin esperar al cronjob
                                if ($factura["status"] == 2) {
                                ?>
                                    <li><a href="javascript:;" onclick="solicitudServidor('facturas','verificarEstatusSAT','idfactura=<?= $factura['idfactura'] ?>','');" class="dropdown-item">Verificar estatus en SAT</a></li>
                                <?
                                }
                                ?>
                                <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/historialpagos.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Historial de Pagos</a></li>
                                <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/historialnotascredito.php?idfactura=<?= $factura['idfactura'] ?>" class="dropdown-item">Historial de Notas de Crédito</a></li>
                            </ul>
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
                    <? if ($facturas["pagina"] > 2) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                    <? } ?>
                    <? if ($facturas["pagina"] > 1) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $facturas['pagina'] - 1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                    <? } ?>
                    <?
                    $i = 0;
                    while ($i < $facturas["maxpaginas"]) {
                        $page = $facturas["pagina"] - ($facturas["maxpaginas"] - $i);
                        if ($page > 0) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i++;
                    }
                    ?>
                    <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $facturas['pagina']; ?>);" href="javascript:;"><? echo $facturas["pagina"]; ?></a></li>
                    <?
                    $i = $facturas["maxpaginas"];
                    while ($i > 0) {
                        $page = $facturas["pagina"] + ($facturas["maxpaginas"] - ($i - 1));
                        if ($page <= $facturas["numpaginas"]) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i--;
                    }
                    ?>
                    <? if ($facturas["pagina"] < $facturas["numpaginas"]) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($facturas['pagina']) + 1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                    <? } ?>
                    <? if ($facturas["pagina"] < ($facturas["numpaginas"] - 1)) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $facturas['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
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
            <?= $facturas["mensaje"] ?>
        </div>
    </div>
<?
}
?>