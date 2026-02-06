<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Compras.php");

$claseCompras = new Compras();
$compras = $claseCompras->obtenerCompras($_POST);
// $compras["respuesta"] = "ERROR";
// $compras["mensaje"] = "No se encontraron compras";

if ($compras["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="50">Nombre</th>
                    <th>Usuario</th>
                    <th>Proveedor</th>
                    <th>Status</th>
                    <th>Fecha</th>
                    <?
                    if ($_POST["slcStatus"] == "3") {
                    ?>
                        <th width="150">Fecha de Recepción</th>
                    <?
                    }
                    ?>
                    <th style="width: 180px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($compras["compras"] as $compra) {
                ?>
                    <tr>
                        <td><?= $compra["idcompra"] ?></td>
                        <td><?= $compra["usuario"] ?></td>
                        <td><?= $compra["proveedor"] ?></td>
                        <td><?= (($compra["status"] == "R") ? "Recibida" : (($compra["status"] == "P") ? "Parcialmente Recibida" : (($compra["status"] == "C") ? "Cancelada" : "Sin Recibir"))) ?></td>
                        <td><?= fecha_formateada($compra["fecha"]) ?></td>
                        <?
                        if ($_POST["slcStatus"] == "3") {
                        ?>
                            <td><?= fecha_formateada($compra["fecharecepcion"]) ?></td>
                        <?
                        }
                        ?>
                        <td class="text-end">
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/compras/ver.php?idcompra=<?= $compra["idcompra"] ?>" data-toggle="tooltip" title="ver compra" class="btn btn-success btn-sm"><i class="uil uil-eye"></i></a>
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/compras/historialrecepciones.php?idcompra=<?= $compra["idcompra"] ?>" data-toggle="tooltip" title="Historial recepciones" class="btn btn-info btn-sm"><i class="uil uil-file-edit-alt"></i></a>
                            <!-- <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/compras/recibirproductos.php?idcompra=<?= $compra["idcompra"] ?>" data-toggle="tooltip" title="recibir productos" class="btn btn-primary btn-sm"><i class="uil uil-cube"></i></a> -->
                            <!-- <div class="dropdown"> -->
                            <button data-toggle="tooltip" title="descargar" class="btn btn-sm btn-secondary" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="uil uil-download-alt"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item" href="javascript:;" onClick="descargarCompraExcel(<?= $compra["idcompra"] ?>);">Excel</a></li>
                                <li><a class="dropdown-item" href="/modulos/compras/descargarcompraspdf.php?idcompra=<? echo $compra["idcompra"]; ?>" target="_blank">PDF</a></li>
                            </ul>
                            <!-- </div> -->
                        </td>
                    </tr>
                <?
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $compras["mensaje"] ?>
        </div>
    </div>
<?
}
?>