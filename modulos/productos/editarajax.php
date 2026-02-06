<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");

$_POST["idproducto"] = $_GET["idproducto"];

$claseProductos = new Productos();
$claseProveedores = new Proveedores();
$claseTiposProducto = new TiposProducto();

$producto = $claseProductos->obtenerProducto($_POST)["producto"];
$proveedores = $claseProveedores->obtenerProveedores($_POST);
$tiposproducto = $claseTiposProducto->obtenerTiposProducto($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Producto</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $producto["idproducto"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $producto["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="slcTipoProducto" class="form-label">Tipo de Producto<span>*</span></label>
            <select name="slcTipoProducto" id="slcTipoProducto" class="form-control requerido" data-mensajeerror="Debes indicar el tipo de producto">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($tiposproducto["tiposproducto"] as $tipoproducto) {
                    ?>
                    <option value="<?= $tipoproducto["idtipoproducto"] ?>" <? if($tipoproducto["idtipoproducto"]==$producto["idtipoproducto"]){?> selected <?} ?>><?= $tipoproducto["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcProveedor" class="form-label">Proveedor<span>*</span></label>
            <select name="slcProveedor" id="slcProveedor" class="form-control requerido" data-mensajeerror="Debes indicar el proveedor">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($proveedores["proveedores"] as $proveedor) {
                    ?>
                    <option value="<?= $proveedor["idproveedor"] ?>" <? if($proveedor["idproveedor"]==$producto["idproveedor"]){?> selected <?} ?> ><?= $proveedor["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="txtClaveProveedor" class="form-label">Clave Proveedor</label>
            <input type="text" class="form-control" name="txtClaveProveedor" id="txtClaveProveedor" placeholder="Ingresa la clave" autocomplete="off" value="<?= $producto["claveproveedor"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Tipo<span>*</span></label>
            <input type="radio" name="slcTipo" id="slcTipo1" value="P" class="requerido" data-mensajeerror="Debes indicar un tipo" <? if($producto["tipo"]=="P"){?> checked <?} ?> >&nbsp;&nbsp;Producto 
            <input type="radio" name="slcTipo" id="slcTipo2" value="S" class="requerido" data-mensajeerror="Debes indicar un tipo" <? if($producto["tipo"]=="S"){?> checked <?} ?> >&nbsp;&nbsp;Servicio
        </div>
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Tienda</label>
            <input type="radio" name="rdTienda" id="rdTienda1" value="1" onClick="cambiarDiv2(this.value,'divPrecio');"<? if($producto["tienda"]==1){?> checked <?} ?>>&nbsp;&nbsp;Sí
            <input type="radio" name="rdTienda" id="rdTienda1" value="2" onClick="cambiarDiv2(this.value,'divPrecio');"<? if($producto["tienda"]==2){?> checked <?} ?>>&nbsp;&nbsp;No
        </div>
        <div id="divPrecio" <? if($producto["tienda"]==2){?>style="display:none;"<?} ?>>
        <!-- son requeridos pero solo si son visibles -->
            <div class="mb-3">
                <label for="txtPrecio" class="form-label">Precio<span>*</span></label>
                <input type="text" class="form-control" name="txtPrecio" id="txtPrecio" placeholder="Ingresa el precio" autocomplete="off" data-mensajeerror="Debes indicar el precio" value="<?= (($producto["precio"]==0) ? "" : $producto["precio"]) ?>">
            </div>
            <div class="mb-3">
                <label for="txtCodigo" class="form-label">Código de Barras<span>*</span></label>
                <input type="text" class="form-control" name="txtCodigo" id="txtCodigo" placeholder="0000" autocomplete="off" data-mensajeerror="Debes indicar el código de barras" value="<?= $producto["codigobarras"] ?>">
            </div>
            <div class="mb-3">
                <label for="txtNombre" class="form-label">¿Tarjeta de Regalo?<span>*</span></label>
                <input type="radio" name="rdTarjetaRegalo" id="rdTarjetaRegalo1" value="0" <? if($producto["tarjetaregalo"]==0){?> checked <?} ?>>&nbsp;&nbsp;No
                <input type="radio" name="rdTarjetaRegalo" id="rdTarjetaRegalo2" value="1" <? if($producto["tarjetaregalo"]==1){?> checked <?} ?>>&nbsp;&nbsp;Sí
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>