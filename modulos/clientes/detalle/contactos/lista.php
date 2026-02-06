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
                <th>Puesto</th>
                <th>Correo Electrónico</th>
                <th>Teléfono</th>
                <th style="width: 180px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($contactos["contactos"] as $contacto){
            ?>
            <tr>
                <td><?= $contacto["nombre"] ?></td>
                <td><?= $contacto["puesto"] ?></td>
                <td><?= $contacto["correo"] ?></td>
                <td><?= $contacto["telefono"] ?></td>
                <td>
                    <a href="javascript:;" class="btn btn-secondary btn-sm waves-effect waves-light" onClick="cargarContacto(<?= $contacto["idcontacto"] ?>)">Editar</a>
                    <a href="javascript:;" onclick="solicitudServidor('contactos','eliminar','idcontacto=<?= $contacto['idcontacto'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm">Eliminar</a>
                    <!-- <i class="uil uil-times"></i> -->
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