<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<script>
    function validarCantidad(){
        if (parseInt($("#txtCantidad").val()) >= parseInt($("#cantidad").val()) ) {
            Swal.fire("Cantidad Incorrecta","Debes introducir una cantidad menor que el total del producto","error")
            .then((result) => {
                $("#txtCantidad").focus();
            });
        } else {
            validarFormulario('formCantidad');
            // console.log("entra");
        }
    }
</script>

<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title"><?= $_GET["nombre"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formCantidad" name="formCantidad">
        <input type="hidden" name="controlador" id="controlador" value="solicitudes">
        <input type="hidden" name="accion" id="accion" value="dividir">
        <input type="hidden" name="idproveedor" value="<?= $_GET["idproveedor"]; ?>">
        <input type="hidden" name="idsolicitudcompra" value="<?= $_GET["idsolicitudcompra"]; ?>">
        <input type="hidden" name="cantidad" id="cantidad" value="<?= $_GET["cantidad"]; ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtCantidad" class="form-label">Cantidad<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCantidad" id="txtCantidad" placeholder="Ingresa la cantidad" autocomplete="off" data-mensajeerror="Debes indicar la cantidad">
        </div>
        <button type="button" onclick="validarCantidad();" class="btn btn-primary">Guardar</button>
    </form>
</div>