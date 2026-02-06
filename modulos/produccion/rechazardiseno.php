<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Rechazar diseño</h4><small>Indica la razón del rechazo de este diseño.</small>
        </div>
    </div>
    <hr>
    <form name="formRechazo" id="formRechazo">
        <input type="hidden" name="controlador" id="controlador" value="produccion">
        <input type="hidden" name="accion" value="rechazardiseno">
        <input type="hidden" name="idsolicituddiseno" value="<? echo $_GET["idsolicituddiseno"]; ?>">
        <input type="hidden" name="idespecificacion" value="<? echo $_GET["idespecificacion"]; ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="form-group mt-2">
            <textarea name="txtRazon" id="txtRazon" rows="8" class="form-control requerido" data-mensajeerror="Debes indicar alguna razón de rechazo"></textarea>
        </div>
        <div class="form-group mt-2 text-center">
            <button type="button" class="btn btn-primary waves-light waves-effect" onClick="validarFormulario('formRechazo');">Enviar</button>
        </div>
    </form>
</div>