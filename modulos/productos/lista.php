<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");

$claseProductos = new Productos();
$claseUsuarios = new Usuarios();
$productos = $claseProductos->obtenerProductos($_POST);

if($productos["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th width="30">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" role="switch" checked onchange="cambiarChecks(this,'chkProducto')">
                        <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                    </div>
                </th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Código de Barras</th>
                <th style="width: 300px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($productos["productos"] as $producto){
                $precio = (($producto["precio"]>0) ? "$" . number_format($producto["precio"],2) : "N/A");
                ?>
                <tr id="trProducto<?= $producto["idproducto"]; ?>">
                    <td>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input chkProducto" role="switch" name="chkProductos[]" checked value="<?= $producto["idproducto"] ?>">
                            <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                        </div>
                    </td>
                    <td><?= $producto["nombre"] ?></td>
                    <td><?= $precio ?></td>
                    <td><?= $producto["codigobarras"] ?></td>
                    <td>
                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/productos/kardex.php?idproducto=<?= $producto["idproducto"] ?>" data-toggle="tooltip" title="kardex" class="btn btn-info btn-sm"><i class="uil uil-file-edit-alt"></i></a>
                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/productos/existencias.php?idproducto=<?= $producto["idproducto"] ?>" data-toggle="tooltip" title="existencias" class="btn btn-info btn-sm"><i class="uil uil-chart-bar"></i></a>
                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/productos/configuraretiqueta.php?idproducto=<?= $producto["idproducto"] ?>" data-toggle="tooltip" title="etiqueta" class="btn btn-secondary btn-sm"><i class="uil uil-label-alt"></i></a>
                        <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/productos/masopciones.php?idproducto=<?= $producto["idproducto"] ?>" data-toggle="tooltip" title="mas opciones" class="btn btn-success btn-sm"><i class="uil uil-eye"></i></a>
                        <a href="/productos/editar/<?= $producto["idproducto"] ?>" data-toggle="tooltip" title="editar" class="btn btn-primary btn-sm"><i class="uil uil-edit"></i></a>
                        <?
                        // si el producto todavía tiene existencias en alguno de los almacenes, no se puede borrar ni siquiera por usuario tipo administrador
                        if ($claseUsuarios->esAdministrador($_SESSION["usuario"]["idusuario"]) && !$claseProductos->tieneExistencias($producto["idproducto"])) {
                            ?>
                            <a href="javascript:;" onclick="solicitudServidor('productos','eliminar','idproducto=<?= $producto['idproducto'] ?>','¿Deseas eliminar el registro?','');" data-toggle="tooltip" title="eliminar" class="btn btn-danger btn-sm"><i class="uil uil-times"></i></a>
                            <?
                        }
                        ?>
                    </td>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
</div>
<div class="row mt-3">
    <div class="col-12 text-end">
        <nav>
            <ul class="pagination">
                <? if($productos["pagina"]>2){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(1);" href="javascript:;" aria-label="Primera"><span aria-hidden="true">&lt;&lt;</span> <span class="sr-only">Primera</span></a></li>
                <? } ?>
                <? if($productos["pagina"]>1){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $productos['pagina']-1; ?>);" href="javascript:;" aria-label="Anterior"><span aria-hidden="true">&lt;</span> <span class="sr-only">Anterior</span></a></li>
                <? } ?>
                <?
                $i = 0;
                while($i<$productos["maxpaginas"]){
                    $page = $productos["pagina"]-($productos["maxpaginas"]-$i);
                    if($page>0){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                    }
                    $i++;
                }
                ?>
                <li class="page-item active"><a class="page-link" onclick="cambiarPagina(<? echo $productos['pagina']; ?>);" href="javascript:;"><? echo $productos["pagina"]; ?></a></li>
                <?
                $i = $productos["maxpaginas"];
                while($i>0){
                    $page = $productos["pagina"]+($productos["maxpaginas"]-($i-1));
                    if($page<=$productos["numpaginas"]){
                    ?>
                    <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $page; ?>);" href="javascript:;"><? echo $page; ?></a></li>
                    <?
                    }
                    $i--;
                }
                ?>
                <? if($productos["pagina"]<$productos["numpaginas"]){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo ($productos['pagina'])+1; ?>);" href="javascript:;" aria-label="Siguiente"><span aria-hidden="true">&gt;</span> <span class="sr-only">Siguiente</span></a></li>
                <? } ?>
                <? if($productos["pagina"]<($productos["numpaginas"]-1)){ ?>
                <li class="page-item"><a class="page-link" onclick="cambiarPagina(<? echo $productos['numpaginas']; ?>);" href="javascript:;" aria-label="Ultima"><span aria-hidden="true">&gt;&gt;</span> <span class="sr-only">Ultima</span></a></li>
                <? } ?>
            </ul>
        </nav>
    </div>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $productos["mensaje"] ?>
    </div>
</div>
<?
}
?>