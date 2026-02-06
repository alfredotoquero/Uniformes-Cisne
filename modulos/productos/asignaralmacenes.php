<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseAlmacenes = new Almacenes();
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);

$_POST["idproducto"] = $_GET["idproducto"];

$claseProductos = new Productos();

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Almacenes</h4>
        </div>
    </div>
    <hr>
    <form id="formAlmacenes" name="formAlmacenes">
        <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
        <input type="hidden" name="accion" id="accion" value="asignaralmacenes">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="chkAlmacenes" class="form-label">Almacenes<span>*</span></label>
            <?
            foreach ($almacenes["almacenes"] as $almacen) {
                $_POST["idalmacen"] = $almacen["idalmacen"];
                ?>
                <div class="row">
                    <div class="col-6">
                        <?= $almacen["nombre"] ?>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" name="chkAlmacenes[]" id="chkAlmacen<?= $almacen["idalmacen"] ?>" data-switch="none" value="<?= $almacen["idalmacen"]; ?>" <? if($claseProductos->tieneAlmacenAsignado($_POST)){?> checked <?} ?> >
                        <label for="chkAlmacen<?= $almacen["idalmacen"] ?>" data-on-label="" data-off-label=""></label>
                    </div>
                </div>
                <?
            }
            ?>
        </div>
        <button type="button" onclick="validarFormulario('formAlmacenes');" class="btn btn-primary">Guardar</button>
    </form>
</div>