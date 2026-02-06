<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Usuario</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="usuarios">
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
            <label for="chkPermisoAdministrador" class="form-label">Permiso de Administrador</label>
            <input type="checkbox" name="chkPermisoAdministrador" id="chkPermisoAdministrador" value="1" data-switch="none">
            <label for="chkPermisoAdministrador" data-on-label="" data-off-label=""></label>
        </div>
        <div class="mb-3">
            <label for="slcFormato" class="form-label">Formato<span>*</span></label>
            <select class="form-control requerido" name="slcFormato" id="slcFormato" data-mensajeerror="Debes indicar el formato">
                <option value="0">--Seleccionar--</option>
                <option value="uniformes">Uniformes</option>
                <option value="playeras">Playeras</option>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>