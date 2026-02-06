<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");

$claseUsuarios = new Usuarios();
$usuarios = $claseUsuarios->obtenerUsuarios($_POST);

if($usuarios["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Status</th>
                <th style="width: 240px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($usuarios["usuarios"] as $usuario){
            ?>
            <tr>
                <td><?= $usuario["nombre"] ?></td>
                <td><?= $usuario["usuario"] ?></td>
                <td><?= $usuario["status"] ?></td>
                <td>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="modulos/usuarios/permisos.php?idusuario=<?= $usuario["idusuario"]; ?>" data-toggle="tooltip" title="permisos" class="btn btn-warning btn-sm"><i class="uil uil-lock"></i></a>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="modulos/usuarios/almacenes.php?idusuario=<?= $usuario["idusuario"]; ?>" data-toggle="tooltip" title="almacenes" class="btn btn-success btn-sm"><i class="uil uil-home-alt"></i></a>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/usuarios/editar.php?idusuario=<?= $usuario["idusuario"]; ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('usuarios','eliminar','idusuario=<?= $usuario['idusuario'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $usuarios["mensaje"] ?>
    </div>
</div>
<?
}
?>