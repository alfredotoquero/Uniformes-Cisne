<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$_POST["idtienda"] = $_GET["idtienda"];

$claseTiendas = new Tiendas();
$tienda = $claseTiendas->obtenerTienda($_POST)["tienda"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Tienda</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="tiendas">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idtienda" id="idtienda" value="<?= $_GET["idtienda"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $tienda["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="imgLogo" class="form-label">Logo</label>
            <input type="file" class="form-control" name="imgLogo" id="imgLogo">
        </div>
        <hr>
        <strong>Servidor SMTP</strong>
        <div class="mb-3">
            <label for="txtSMTP_Correo" class="form-label">Correo<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtSMTP_Correo" id="txtSMTP_Correo" placeholder="Ingresa el correo SMTP" autocomplete="off" data-mensajeerror="Debes indicar el correo SMTP" value="<?= $tienda["smtp_correo"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtSMTP_Password" class="form-label">Password (solo si se requiere modificar)</label>
            <input type="password" class="form-control" name="txtSMTP_Password" id="txtSMTP_Password" placeholder="Ingresa el password SMTP" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="txtSMTP_Nombre" class="form-label">Remitente<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtSMTP_Nombre" id="txtSMTP_Nombre" placeholder="Ingresa el remitente SMTP" autocomplete="off" data-mensajeerror="Debes indicar el remitente SMTP" value="<?= $tienda["smtp_nombre"] ?>">
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>