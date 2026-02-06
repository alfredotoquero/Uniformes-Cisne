<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");

// $_POST["idcatalogo"] = $_POST["idcatalogo"];

$claseCatalogos = new Catalogos();
$valores = $claseCatalogos->obtenerValores($_POST);

if($valores["respuesta"]=="OK"){
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
            foreach($valores["valores"] as $valores){
            ?>
            <tr>
                <td><?= $valores["nombre"] ?></td>
                <td>
                    <!-- <a href="/contactos/<?= $valores["idvalor"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-users-alt"></i></a> -->
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/catalogos/valores/editar.php?idvalor=<?= $valores["idvalor"] ?>&idcatalogo=<?= $_POST["idcatalogo"]; ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('catalogos','eliminarvalor','idvalor=<?= $valores['idvalor'] ?>&idcatalogo=<?= $_POST['idcatalogo'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $valores["mensaje"] ?>
    </div>
</div>
<?
}
?>