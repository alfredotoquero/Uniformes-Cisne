<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TipoCambio.php");

$claseTipoCambio = new TipoCambio();
$tipocambio = $claseTipoCambio->obtenerTipoCambio()["tipocambio"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Tipo de Cambio</h4>
        </div>
    </div>
    <hr>
    <form id="formCambio" name="formCambio">
        <input type="hidden" name="controlador" id="controlador" value="tipocambio">
        <input type="hidden" name="accion" id="accion" value="actualizar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtCambio" class="form-label">Tipo de cambio<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCambio" id="txtCambio" placeholder="Ingresa el tipo de cambio" autocomplete="off" data-mensajeerror="Debes indicar el tipo de cambio" value="<?= $tipocambio["pesos"] ?>">
        </div>
        <button type="button" onclick="validarFormulario('formCambio');" class="btn btn-primary">Guardar</button>
    </form>
</div>