<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");

$_POST["idcatalogo"] = $_GET["idcatalogo"];

$claseCatalogos = new Catalogos();
$catalogo = $claseCatalogos->obtenerCatalogo($_POST)["catalogo"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Catalogo</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="catalogos">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idcatalogo" id="idcatalogo" value="<?= $_GET["idcatalogo"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $catalogo["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Valor Multiple<span>*</span></label>
            <input type="radio" name="rdMultiple" id="rdMultiple1" value="1" <? if($catalogo["multiple"]){?> checked <?} ?>>&nbsp;&nbsp;Sí
            <input type="radio" name="rdMultiple" id="rdMultiple2" value="0" <? if(!$catalogo["multiple"]){?> checked <?} ?>>&nbsp;&nbsp;No
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>