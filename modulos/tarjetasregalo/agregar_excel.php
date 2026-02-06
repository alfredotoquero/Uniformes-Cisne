<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Tarjetas de Regalo con Excel</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregarExcel" name="formAgregarExcel" enctype="multipart/form-data">
        <input type="hidden" name="controlador" id="controlador" value="tarjetasregalo">
        <input type="hidden" name="accion" id="accion" value="agregarexcel">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="flCodigos" class="form-label">Archivo (csv o xlsx)<span>*</span></label>
            <input type="file" class="form-control requerido kv-fileinput-caption" name="flCodigos" id="flCodigos" onkeydown="return false" onpaste="return false" accept=".xlsx, .csv" data-mensajeerror="Debes agregar un documento">
        </div>
        <button type="button" onclick="validarFormulario('formAgregarExcel');" class="btn btn-primary">Guardar</button>
    </form>
</div>