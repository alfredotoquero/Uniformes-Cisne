<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");

$claseProveedores = new Proveedores();
$proveedores = $claseProveedores->obtenerProveedores($_POST);

if($proveedores["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($proveedores["proveedores"] as $proveedor){
            ?>
            <tr>
                <td><?= $proveedor["nombre"] ?></td>
                <td>
                    <!-- <a href="/contactos/<?= $proveedor["idproveedor"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/proveedores/editar.php?idproveedor=<?= $proveedor["idproveedor"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('proveedores','eliminar','idproveedor=<?= $proveedor['idproveedor'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $proveedores["mensaje"] ?>
    </div>
</div>
<?
}
?>