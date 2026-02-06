<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Contacto</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="contactos">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["idcliente"]; ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtContacto" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtContacto" id="txtContacto" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="txtCorreo" class="form-label">Correo electrónico<span>*</span></label>
            <input type="email" class="form-control requerido" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar un correo electrónico válido">
        </div>
        <div class="mb-3">
            <label for="txtTelefono" class="form-label">Teléfono<span>*</span></label>
            <input type="tel" class="form-control requerido" name="txtTelefono" id="txtTelefono" placeholder="Ingresa el teléfono" autocomplete="off" data-mensajeerror="Debes indicar un teléfono válido">
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>