<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Vendedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");

$claseVendedores = new Vendedores();
$vendedores = $claseVendedores->obtenerVendedores($_POST);

$claseSucursales = new Sucursales();

if($vendedores["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Sucursal</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($vendedores["vendedores"] as $vendedor){
                $_POST["idsucursal"] = $vendedor["idsucursal"];
                $sucursal = $claseSucursales->obtenerSucursal($_POST)["sucursal"];
            ?>
            <tr>
                <td><?= $vendedor["nombre"] ?></td>
                <td><?= $vendedor["usuario"] ?></td>
                <td><?= $sucursal["nombre"] ?></td>
                <td>
                    <!-- <a href="/contactos/<?= $vendedor["idvendedor"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/vendedores/editar.php?idvendedor=<?= $vendedor["idvendedor"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('vendedores','eliminar','idvendedor=<?= $vendedor['idvendedor'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
                </td>
            </tr>
            <?
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
        <?= $vendedores["mensaje"] ?>
    </div>
</div>
<?
}
?>