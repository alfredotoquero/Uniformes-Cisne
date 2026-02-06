<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Personalizaciones.php");

$clasePersonalizaciones = new Personalizaciones();
$personalizaciones = $clasePersonalizaciones->obtenerPersonalizaciones($_POST);

if($personalizaciones["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($personalizaciones["personalizaciones"] as $personalizacion){
            ?>
            <tr>
                <td><?= $personalizacion["nombre"] ?></td>
                <td>$<?= number_format($personalizacion["precio"],2) ?></td>
                <td>
                    <!-- <a href="/contactos/<?= $personalizacion["idpersonalizacion"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/personalizaciones/editar.php?idpersonalizacion=<?= $personalizacion["idpersonalizacion"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('personalizaciones','eliminar','idpersonalizacion=<?= $personalizacion['idpersonalizacion'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $personalizaciones["mensaje"] ?>
    </div>
</div>
<?
}
?>