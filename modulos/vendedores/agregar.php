<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Vendedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");

$claseVendedores = new Vendedores();
$claseSucursales = new Sucursales();

$vendedores = $claseVendedores->obtenerVendedores($_POST);
$sucursales = $claseSucursales->obtenerSucursales($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Vendedor</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="vendedores">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="txtUsuario" class="form-label">Usuario<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtUsuario" id="txtUsuario" placeholder="Ingresa el usuario" autocomplete="off" data-mensajeerror="Debes indicar el usuario">
        </div>
        <div class="mb-3">
            <label for="txtPassword" class="form-label">Contrase&ntilde;a<span>*</span></label>
            <input type="password" class="form-control requerido" name="txtPassword" id="txtPassword" placeholder="Ingresa la contrase&ntilde;a" autocomplete="off" data-mensajeerror="Debes indicar la contrase&ntilde;a">
        </div>
        <div class="mb-3">
            <label for="slcSucursal" class="form-label">Sucursal<span>*</span></label>
            <select name="slcSucursal" id="slcSucursal" class="form-control requerido" data-mensajeerror="Debes indicar la sucursal">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($sucursales["sucursales"] as $sucursal) {
                    ?>
                    <option value="<?= $sucursal["idsucursal"] ?>"><?= $sucursal["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="txtDescuento" class="form-label">Descuento<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtDescuento" id="txtDescuento" placeholder="Ingresa el descuento" autocomplete="off" data-mensajeerror="Debes indicar el descuento">
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>