<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pagos.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$clasePagos = new Pagos();
$pagos = $clasePagos->getPagos($_POST);

$claseUsuarios = new Usuarios();

$idusuario = $_SESSION["usuario"]["idusuario"];

if ($pagos["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="40">#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th style="width: 160px;"></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach ($pagos["pagos"] as $pago) {
                ?>
                    <tr>
                        <td><?= $pago["serie"]." - ".$pago["folio"]; ?></td>
                        <td><?= $pago["cliente"]; ?></td>
                        <td>$<?= number_format($pago["total"],2); ?></td>
                        <td><?= str_replace("<br>"," ",fecha_formateada($pago["registro"])); ?></td>
                        <td>
                            <?php
                            switch ((int)$pago["status"]) {
                                case 1:  echo '<span class="badge bg-success">ACTIVO</span>'; break;
                                case 2:  echo '<span class="badge bg-warning text-dark">PROCESO DE CANCELACIÓN</span>'; break;
                                case 4:  echo '<span class="badge bg-info text-dark">COMPLEMENTO CANCELADO</span>'; break;
                                default: echo '<span class="badge bg-danger">CANCELADO</span>'; break;
                            }
                            ?>
                            <?php if((int)$pago["status"] == 1 && empty($pago["uuid"]) && $pago["tiene_factura"]): ?>
                                <span class="badge bg-warning text-dark" title="Complemento de pago pendiente de timbrar"><i class="fas fa-clock"></i> COMPLEMENTO PENDIENTE</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-secondary btn-sm mb-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Opciones <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:;" onclick="solicitudServidor('pagos','verPDF','idpago=<?= $pago['idpago'] ?>','');" class="dropdown-item">Ver PDF</a></li>
                                <?
                                if (!empty($pago["uuid"])) {
                                ?>
                                    <li><a href="/modulos/pagos/descargarXML.php?idpago=<?= $pago['idpago'] ?>" class="dropdown-item">Descargar XML</a></li>
                                    <li><a href="/modulos/pagos/descargar.php?idpago=<?= $pago['idpago'] ?>" class="dropdown-item">Descargar Pago</a></li>
                                <?
                                }else if ($pago["tiene_factura"] && $pago["status"] == 1) {
                                ?>
                                    <li><a href="javascript:;" onclick="solicitudServidor('pagos','timbrar','idpago=<?= $pago['idpago'] ?>','¿Deseas timbrar el complemento de pago?');" class="dropdown-item">Timbrar</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?
                                }
                                if ($pago["status"] == 1 && !empty($pago["uuid"])) {
                                ?>
                                    <li><a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pagos/cancelar.php?idpago=<?= $pago['idpago'] ?>" class="dropdown-item text-danger">Cancelar complemento</a></li>
                                <?
                                }else if (($pago["status"] == 1 && empty($pago["uuid"])) || $pago["status"] == 4) {
                                ?>
                                    <li><a href="javascript:;" onclick="solicitudServidor('pagos','cancelar','idpago=<?= $pago['idpago'] ?>','¿Deseas cancelar este pago?');" class="dropdown-item text-danger">Cancelar pago</a></li>
                                <?
                                }
                                ?>
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
                    <? if ($pagos["pagina"] > 2) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                    <? } ?>
                    <? if ($pagos["pagina"] > 1) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pagos['pagina'] - 1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                    <? } ?>
                    <?
                    $i = 0;
                    while ($i < $pagos["maxpaginas"]) {
                        $page = $pagos["pagina"] - ($pagos["maxpaginas"] - $i);
                        if ($page > 0) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i++;
                    }
                    ?>
                    <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $pagos['pagina']; ?>);" href="javascript:;"><? echo $pagos["pagina"]; ?></a></li>
                    <?
                    $i = $pagos["maxpaginas"];
                    while ($i > 0) {
                        $page = $pagos["pagina"] + ($pagos["maxpaginas"] - ($i - 1));
                        if ($page <= $pagos["numpaginas"]) {
                    ?>
                            <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                        }
                        $i--;
                    }
                    ?>
                    <? if ($pagos["pagina"] < $pagos["numpaginas"]) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($pagos['pagina']) + 1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                    <? } ?>
                    <? if ($pagos["pagina"] < ($pagos["numpaginas"] - 1)) { ?>
                        <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $pagos['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
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
            <?= $pagos["mensaje"] ?>
        </div>
    </div>
<?
}
?>