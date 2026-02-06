<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Enviar diseño</h4>
        </div>
    </div>
    <hr>
    <form name="formDiseno" id="formDiseno">
        <input type="hidden" name="controlador" id="controlador" value="produccion">
        <input type="hidden" name="accion" value="enviardiseno">
        <input type="hidden" name="idespecificacion" value="<? echo $_GET["idespecificacion"]; ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="form-group">
            <input type="file" name="imgDiseno1" id="imgDiseno1" class="form-control">
        </div>
        <div class="form-group mt-2">
            <input type="file" name="imgDiseno2" id="imgDiseno2" class="form-control">
        </div>
        <div class="form-group mt-2">
            <textarea name="txtComentarios" id="txtComentarios" rows="8" class="form-control"></textarea>
        </div>
        <div class="form-group mt-2 text-center">
            <button type="button" class="btn btn-primary waves-light waves-effect" onClick="validarFormulario('formDiseno');">Enviar Diseño</button>
        </div>
    </form>
</div>