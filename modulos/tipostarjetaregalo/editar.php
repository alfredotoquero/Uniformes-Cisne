<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposTarjetaRegalo.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");

$_POST["idtipo"] = $_GET["idtipo"];

$claseTiposTarjetaRegalo = new TiposTarjetaRegalo();
$claseSucursales = new Sucursales();

$tipotarjetaregalo = $claseTiposTarjetaRegalo->obtenerTipoTarjetaRegalo($_POST)["tipotarjetaregalo"];
$sucursales = $claseSucursales->obtenerSucursales($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Tipo de Tarjeta de Regalo</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="tipostarjetaregalo">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idtipo" id="idtipo" value="<?= $_GET["idtipo"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $tipotarjetaregalo["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtPrecio" class="form-label">Precio<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtPrecio" id="txtPrecio" placeholder="Ingresa el precio" autocomplete="off" data-mensajeerror="Debes indicar el precio" value="<?= $tipotarjetaregalo["precio"] ?>">
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>