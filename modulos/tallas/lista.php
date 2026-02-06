<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");

$claseTallas = new Tallas();
$_POST["orden"] = 2;
$tallas = $claseTallas->obtenerTallas($_POST);

if($tallas["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <!-- Posicion -->
                <!-- <th></th> -->
                <th>Nombre</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($tallas["tallas"] as $talla){
            ?>
            <tr>
                <!-- <td></td> -->
                <td><?= $talla["nombre"] ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/tallas/editar.php?idtalla=<?= $talla["idtalla"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('tallas','eliminar','idtalla=<?= $talla['idtalla'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $tallas["mensaje"] ?>
    </div>
</div>
<?
}
?>