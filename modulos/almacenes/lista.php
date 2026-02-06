<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");

$claseAlmacenes = new Almacenes();
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);

if($almacenes["respuesta"]=="OK"){
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
            foreach($almacenes["almacenes"] as $almacen){
            ?>
            <tr>
                <td><?= $almacen["nombre"] ?></td>
                <td>
                    <!-- <a href="/contactos/<?= $almacen["idalmacen"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/almacenes/editar.php?idalmacen=<?= $almacen["idalmacen"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('almacenes','eliminar','idalmacen=<?= $almacen['idalmacen'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $almacenes["mensaje"] ?>
    </div>
</div>
<?
}
?>