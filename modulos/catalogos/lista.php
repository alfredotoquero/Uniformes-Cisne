<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");

// $_POST["idcatalogo"] = $_GET["idcatalogo"];

$claseCatalogos = new Catalogos();
$catalogos = $claseCatalogos->obtenerCatalogos($_POST);

if($catalogos["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th style="width: 180px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($catalogos["catalogos"] as $catalogo){
            ?>
            <tr>
                <td><?= $catalogo["nombre"] ?></td>
                <td>
                    <a href="/catalogos/valores/<?= $catalogo["idcatalogo"]; ?>" class="btn btn-info btn-sm"><i class="uil uil-file-plus"></i></a>
                    <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/catalogos/editar.php?idcatalogo=<?= $catalogo["idcatalogo"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                    <a href="javascript:;" onclick="solicitudServidor('catalogos','eliminar','idcatalogo=<?= $catalogo['idcatalogo'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
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
        <?= $catalogos["mensaje"] ?>
    </div>
</div>
<?
}
?>