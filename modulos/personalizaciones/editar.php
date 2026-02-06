<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Personalizaciones.php");

$_POST["idpersonalizacion"] = $_GET["idpersonalizacion"];

$clasePersonalizaciones = new Personalizaciones();
$personalizacion = $clasePersonalizaciones->obtenerPersonalizacion($_POST)["personalizacion"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Personalización</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="personalizaciones">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idpersonalizacion" id="idpersonalizacion" value="<?= $_GET["idpersonalizacion"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $personalizacion["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtPrecio" class="form-label">Precio<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtPrecio" id="txtPrecio" placeholder="Ingresa el precio" autocomplete="off" data-mensajeerror="Debes indicar el precio" value="<?= $personalizacion["precio"] ?>">
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>