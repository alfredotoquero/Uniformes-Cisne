<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Emisores.php");

$claseEmisores = new Emisores();
$emisores = $claseEmisores->obtenerEmisores($_POST);

if ($emisores["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Razon social</th>
                    <th>RFC</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($emisores["emisores"] as $emisor) {
                ?>
                    <tr>
                        <td><?= $emisor["razon_social"] ?></td>
                        <td><?= $emisor["rfc"] ?></td>
                        <td>
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/emisores/editar.php?idemisor=<?= $emisor["idemisor"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                            <a href="javascript:;" onclick="solicitudServidor('emisores','eliminar','idemisor=<?= $emisor['idemisor'] ?>','¿Deseas eliminar al emisor?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
            <?= $emisores["mensaje"] ?>
        </div>
    </div>
<?
}
?>