<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Contactos.php");

$claseContactos = new Contactos();
$contactos = $claseContactos->obtenerContactos($_POST);

if($contactos["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Correo Electrónico</th>
                <th>Teléfono</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($contactos["contactos"] as $contacto){
            ?>
            <tr>
                <td><?= $contacto["nombre"] ?></td>
                <td><?= $contacto["correo"] ?></td>
                <td><?= $contacto["telefono"] ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/contactos/editar.php?idcontacto=<?= $contacto["idcontacto"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('contactos','eliminar','idcontacto=<?= $contacto['idcontacto'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $contactos["mensaje"] ?>
    </div>
</div>
<?
}
?>