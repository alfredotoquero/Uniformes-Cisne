<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");

$claseSucursales = new Sucursales();
$sucursales = $claseSucursales->obtenerSucursales($_POST);

$claseAlmacenes = new Almacenes();

if ($sucursales["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Almacen</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($sucursales["sucursales"] as $sucursal) {
                    $_POST["idalmacen"] = $sucursal["idalmacen"];
                    $almacen = $claseAlmacenes->obtenerAlmacen($_POST)["almacen"];
                ?>
                    <tr>
                        <td><?= $sucursal["nombre"] ?></td>
                        <td><?= $almacen["nombre"] ?></td>
                        <td>
                            <!-- <a href="/contactos/<?= $sucursal["idsucursal"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                            <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/sucursales/editar.php?idsucursal=<?= $sucursal["idsucursal"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                            <a href="javascript:;" onclick="solicitudServidor('sucursales','eliminar','idsucursal=<?= $sucursal['idsucursal'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
            <?= $sucursales["mensaje"] ?>
        </div>
    </div>
<?
}
?>