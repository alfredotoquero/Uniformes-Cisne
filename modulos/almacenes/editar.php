<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");

$_POST["idalmacen"] = $_GET["idalmacen"];

$claseAlmacenes = new Almacenes();
$almacen = $claseAlmacenes->obtenerAlmacen($_POST)["almacen"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Almacen</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="almacenes">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idalmacen" id="idalmacen" value="<?= $_GET["idalmacen"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $almacen["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="slcReorden" class="form-label">¿Almacén de reorden?<span>*</span></label>
            <select class="form-control" name="slcReorden" id="slcReorden">
                <option value="0" <?= ($almacen["reorden"]==0) ? "selected" : "" ?>>No</option>
                <option value="1" <?= ($almacen["reorden"]==1) ? "selected" : "" ?>>Si</option>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>