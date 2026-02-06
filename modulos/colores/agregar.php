<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");

$claseColores = new Colores();
$colores = $claseColores->obtenerColores($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Color</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="colores">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="slcColorPadre" class="form-label">Color Padre</label>
            <select name="slcColorPadre" id="slcColorPadre" class="form-control select2">
                <option value="<?= 0 ?>">--Seleccionar--</option>
                <?
                foreach ($colores["colores"] as $color) {
                    ?>
                    <option value="<?= $color["idcolor"] ?>"><?= $color["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>
<script>
    $(document).ready(function (e) {
        $(".select2").select2();
    });
</script>