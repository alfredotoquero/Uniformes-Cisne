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
                    <th>Fecha</th>
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
                        <td><?= str_replace("<br>"," ",fecha_formateada($factura["registro"])); ?></td>
                        <td class="text-end">
                            <a href="javascript:;" onclick="solicitudServidor('facturas','verPDF','idfactura=<?= $factura['idfactura'] ?>','');" class="btn btn-info btn-sm mb-1" title="Ver PDF"><i class="uil uil-file-alt"></i></a>
                            <?
                            if ($factura["status"] == 1) {
                            ?>
                                <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/facturas/cancelar.php?idfactura=<?= $factura["idfactura"] ?>" class="btn btn-danger btn-sm mb-1" title="Cancelar"><i class="uil uil-times"></i></a>
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