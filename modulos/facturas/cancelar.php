<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");

$sat = new SAT();
$f = new Facturas();

$factura = $f->getFactura(array(
    "idfactura" => $_GET["idfactura"]
));

$motivoscancelacion = $sat->obtenerMotivosCancelacion()["motivoscancelacion"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <?php
    if($factura["respuesta"]=="OK"){
        $factura = $factura["factura"];
    ?>
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Cancelar factura <?= $factura["serie"]."-".$factura["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formCancelar" name="formCancelar">
        <input type="hidden" name="controlador" id="controlador" value="facturas">
        <input type="hidden" name="accion" id="accion" value="cancelar">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $_SESSION["usuario"]["idusuario"] ?>">
        <input type="hidden" name="idfactura" id="idfactura" value="<?= $_GET["idfactura"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label class="form-label">Cliente</label><br>
            <?= $factura["cliente"] ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha</label><br>
            <?= str_replace("<br>"," ",fecha_formateada($factura["registro"])); ?>
        </div>
        <div class="mb-3">
            <label for="slcMotivoCancelacion" class="form-label">Motivo de cancelación<span>*</span></label>
            <select class="form-control requerido" name="slcMotivoCancelacion" id="slcMotivoCancelacion" data-mensajeerror="Debes indicar el motivo de cancelación" onchange="validarMotivoCancelacion()">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($motivoscancelacion as $motivocancelacion) {
                    ?>
                    <option value="<?= $motivocancelacion["idmotivo"]; ?>" data-uuid="<?= $motivocancelacion["requiere_uuid"] ?>"><?= $motivocancelacion["clave"]." - ".$motivocancelacion["descripcion"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div id="divUUID">
            <div class="mb-3">
                <label id="lblUUID" for="txtUUID" class="form-label">UUID de sustitución<span>*</span></label>
                <input type="text" class="form-control uppercase" name="txtUUID" id="txtUUID" placeholder="Ingresa el UUID de sustitución" autocomplete="off" data-mensajeerror="Debes indicar el UUID de sustitución" maxlength="36">
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formCancelar');" class="btn btn-primary">Cancelar</button>
    </form>
    <?php
    }else{
    ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger"><?= $factura["mensaje"] ?></div>
        </div>
    </div>
    <?
    }
    ?>
</div>
<script>
$(document).ready(function () {
    validarMotivoCancelacion();

    $('#txtUUID',"#formCancelar").mask('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', {
        translation: {
            'A': { pattern: /[0-9A-Fa-f]/ }
        }
    });
});

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('uppercase')) {
        e.target.value = e.target.value.toUpperCase();
    }
});

function validarMotivoCancelacion(){
    if($("#slcMotivoCancelacion option:selected").data("uuid")==1){
        $("#divUUID").show();
        $("#txtUUID","#formCancelar").addClass("requerido");
    }else{
        $("#divUUID").hide();
        $("#txtUUID","#formCancelar").value("");
        $("#txtUUID","#formCancelar").removeClass("requerido");
    }
}
</script>