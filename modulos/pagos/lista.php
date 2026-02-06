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
                            <a href="javascript:;" onclick="solicitudServidor('pagos','verPDF','idpago=<?= $pago['idpago'] ?>','');" class="btn btn-info btn-sm mb-1" title="Ver PDF"><i class="uil uil-file-alt"></i></a>
                            <?
                            if ($pago["status"] == 1) {
                            ?>
                                <a href="javascript:;" onclick="solicitudServidor('pagos','cancelar','idpago=<?= $pago['idpago'] ?>','¿Deseas cancelar el pago?','');" class="btn btn-danger btn-sm mb-1" title="Cancelar"><i class="uil uil-times"></i></a>
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