<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");

$claseTiposProducto = new TiposProducto();
$tiposproducto = $claseTiposProducto->obtenerTiposProducto($_POST);

if($tiposproducto["respuesta"]=="OK"){
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
            foreach($tiposproducto["tiposproducto"] as $tipoproducto){
            ?>
            <tr>
                <td><?= $tipoproducto["nombre"] ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/tiposproducto/editar.php?idtipoproducto=<?= $tipoproducto["idtipoproducto"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('tiposproducto','eliminar','idtipoproducto=<?= $tipoproducto['idtipoproducto'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $tiposproducto["mensaje"] ?>
    </div>
</div>
<?
}
?>