<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");

$claseProductos = new Productos();
$claseTallas = new Tallas();

$_POST["idproducto"] = $_GET["idproducto"];
$producto = $claseProductos->obtenerProducto($_POST)["producto"];

?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Etiqueta del producto: <? echo $producto["nombre"]; ?></h4>
        </div>
    </div>
    <hr>
    <form id="formVariante" name="formVariante">
        <!-- <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="accion" id="accion" value="guardarvariantes"> -->
        <input type="hidden" name="idproducto" value="<? echo $producto["idproducto"]; ?>">
        
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Etiqueta</label>
            <select name="slcTalla" id="slcTalla" class="form-control">
                <option value="0">PRECIO REGULAR</option>
                <?
                $tallas = $claseProductos->obtenerTallasVariante($_POST);
                foreach ($tallas["tallas"] as $talla) {
                    $_POST["idtalla"] = $talla["idtalla"];
                    $talla = $claseTallas->obtenerTalla($_POST)["talla"];
                    ?>
                    <option value="<?= $talla["idtalla"] ?>"><?= $talla["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        
        <button type="button" onclick="cargarPDF('formVariante','/modulos/productos/','etiqueta');" class="btn btn-primary">MOSTRAR ETIQUETA</button>
    </form>
</div>