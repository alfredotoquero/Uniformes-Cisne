<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");

$sat = new SAT();
$f   = new Facturas();
$c   = new Clientes();
$e   = new Emisores();

$facturaResp = $f->getFactura(array("idfactura" => $_GET["idfactura"]));

if($facturaResp["respuesta"] != "OK"){
    ?>
    <div style="width:500px;">
        <div class="alert alert-danger"><?= $facturaResp["mensaje"] ?></div>
    </div>
    <?php
    exit;
}

$factura   = $facturaResp["factura"];
$idcliente = (int)$factura["idcliente"];

$razonessociales   = array();
$correoPrecargado  = "";
$nombreCliente     = $factura["cliente"];

if($idcliente > 0){
    $razonessociales = $c->obtenerRazonesSociales($idcliente)["razones"];
    $clienteData     = $c->obtenerCliente(array("idcliente" => $idcliente))["cliente"];
    $partes = array_filter(array($clienteData["correo"], $clienteData["correos_adicionales"]));
    $correoPrecargado = implode(",", $partes);
    $nombreCliente    = $clienteData["nombre"];
}

$regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];
$usoscfdi          = $sat->obtenerUsosCFDI()["usoscfdi"];
$metodospago       = $sat->obtenerMetodosPago()["metodospago"];
$formaspago        = $sat->obtenerFormasPago()["formaspago"];
$emisores          = $e->obtenerEmisores(array())["emisores"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div id="divRefacturar" style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Refacturar pedido #<?= $factura["idpedido"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formRefacturar" name="formRefacturar">
        <input type="hidden" name="controlador" id="controlador" value="facturas">
        <input type="hidden" name="accion" id="accion" value="refacturar">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $_SESSION["usuario"]["idusuario"] ?>">
        <input type="hidden" name="idpedido" id="idpedido" value="<?= (int)$factura["idpedido"] ?>">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $idcliente ?>">
        <input type="hidden" name="idfactura" id="idfactura" value="<?= (int)$_GET["idfactura"] ?>">
        <input type="hidden" name="authToken" id="authToken" value="<?= $_SESSION["authToken"] ?>">
        <div class="mb-3">
            <label class="form-label">Cliente</label><br>
            <?= $nombreCliente ?>
        </div>
        <div id="divRazonSocial">
            <div class="mb-3">
                <label for="slcRazonSocialRef" class="form-label">Razón social<span>*</span></label>
                <select class="form-control requerido" name="slcRazonSocial" id="slcRazonSocialRef" data-mensajeerror="Debes indicar una razón social" onchange="validarRazonSocialRef(this.value)">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach($razonessociales as $razonsocial){
                        ?>
                        <option value="<?= $razonsocial["idrazonsocial"] ?>"><?= $razonsocial["razon_social"]." - ".$razonsocial["rfc"] ?></option>
                        <?
                    }
                    ?>
                    <option value="">Nueva razón social</option>
                </select>
            </div>
        </div>
        <div id="divNuevaRazonSocialRef">
            <div class="mb-3">
                <label for="txtRazonSocialRef" class="form-label">Razón social<span>*</span></label>
                <input type="text" class="form-control uppercase nuevaRazonSocialRef" name="txtRazonSocial" id="txtRazonSocialRef" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social" value="<?= ($idcliente == 0) ? htmlspecialchars($factura["cliente"]) : "" ?>">
            </div>
            <div class="mb-3">
                <label for="txtRFCRef" class="form-label">RFC<span>*</span></label>
                <input type="text" class="form-control uppercase nuevaRazonSocialRef" name="txtRFC" id="txtRFCRef" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar el RFC" value="<?= ($idcliente == 0) ? htmlspecialchars($factura["cliente_rfc"]) : "" ?>">
            </div>
            <div class="mb-3">
                <label for="txtCodigoPostalRef" class="form-label">Código postal<span>*</span></label>
                <input type="text" class="form-control nuevaRazonSocialRef" name="txtCodigoPostal" id="txtCodigoPostalRef" placeholder="Ingresa el código postal" autocomplete="off" data-mensajeerror="Debes indicar el código postal" value="<?= ($idcliente == 0) ? htmlspecialchars($factura["codigo_postal"]) : "" ?>">
            </div>
            <div class="mb-3">
                <label for="slcRegimenFiscalRef" class="form-label">Régimen fiscal<span>*</span></label>
                <select class="nuevaRazonSocialRef select2ref" name="slcRegimenFiscal" id="slcRegimenFiscalRef" data-mensajeerror="Debes indicar el régimen fiscal">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach($regimenesfiscales as $regimenfiscal){
                        ?>
                        <option value="<?= $regimenfiscal["idregimenfiscal"] ?>"><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"] ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="slcUsoCFDIRef" class="form-label">Uso del CFDI<span>*</span></label>
                <select class="nuevaRazonSocialRef select2ref" name="slcUsoCFDI" id="slcUsoCFDIRef" data-mensajeerror="Debes indicar el uso del CFDI">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach($usoscfdi as $usocfdi){
                        ?>
                        <option value="<?= $usocfdi["idusocfdi"] ?>"><?= $usocfdi["usocfdi"]." - ".$usocfdi["descripcion"] ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="txtCorreoRef" class="form-label">Correo electrónico<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreoRef" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar el correo electrónico" value="<?= htmlspecialchars($correoPrecargado) ?>" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*" title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Para enviar a múltiples destinatarios, separa los correos con coma (ej: correo1@ejemplo.com, correo2@ejemplo.com)</small>
        </div>
        <div class="mb-3">
            <label for="txtCorreoAdicionalRef" class="form-label">Correos adicionales <small class="text-muted">(opcional)</small></label>
            <input type="text" class="form-control" name="txtCorreoAdicional" id="txtCorreoAdicionalRef" placeholder="Ingresa correos adicionales" autocomplete="off" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*" title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Correos extra a los que se enviará la factura, separados por coma. No se guardarán en el sistema.</small>
        </div>
        <div class="mb-3">
            <label for="slcMetodoPagoRef" class="form-label">Método de pago<span>*</span></label>
            <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPagoRef" onchange="validarMetodoPagoRef();" data-mensajeerror="Debes indicar el método de pago">
                <option value="0">--Seleccionar--</option>
                <?
                foreach($metodospago as $metodopago){
                    ?>
                    <option value="<?= $metodopago["idmetodopago"] ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcFormaPagoRef" class="form-label">Forma de pago<span>*</span></label>
            <select class="requerido select2ref" name="slcFormaPago" id="slcFormaPagoRef" data-mensajeerror="Debes indicar la forma de pago">
                <option value="0">--Seleccionar--</option>
                <?
                foreach($formaspago as $formapago){
                    ?>
                    <option value="<?= $formapago["idformapago"] ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcEmisorRef" class="form-label">Emisor<span>*</span></label>
            <select class="requerido select2ref" name="slcEmisor" id="slcEmisorRef" data-mensajeerror="Debes indicar un emisor">
                <option value="0">--Seleccionar--</option>
                <?
                foreach($emisores as $emisor){
                    ?>
                    <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"]." - ".$emisor["rfc"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <button type="button" onclick="validarFormRefacturar();" class="btn btn-primary">Refacturar</button>
    </form>
</div>
<script>
$(document).ready(function () {
    $(".select2ref", "#formRefacturar").select2({
        dropdownParent: $('#divRefacturar'),
        width: '100%'
    });

    <? if($idcliente > 0){ ?>
    $("#divRazonSocial").show();
    $("#divNuevaRazonSocialRef").hide();
    $("#slcRazonSocialRef").val(<?= (int)$factura["idrazonsocial"] ?>);
    <? }else{ ?>
    $("#divRazonSocial").hide();
    $("#divNuevaRazonSocialRef").show();
    $("#slcRazonSocialRef").removeClass("requerido");
    $(".nuevaRazonSocialRef").addClass("requerido");
    <? if(!empty($factura["idregimenfiscal"])){ ?>
    $("#slcRegimenFiscalRef").val(<?= (int)$factura["idregimenfiscal"] ?>).trigger('change.select2');
    <? } ?>
    <? if(!empty($factura["idusocfdi"])){ ?>
    $("#slcUsoCFDIRef").val(<?= (int)$factura["idusocfdi"] ?>).trigger('change.select2');
    <? } ?>
    <? } ?>

    $("#slcEmisorRef").val(<?= (int)$factura["idemisor"] ?>).trigger('change.select2');

    var idmetodopago = <?= (int)$factura["idmetodopago"] ?>;
    var idformapago  = <?= (int)$factura["idformapago"] ?>;
    $("#slcMetodoPagoRef").val(idmetodopago);
    if(idmetodopago == 1){ // PPD
        $('#slcFormaPagoRef option[value="21"]').prop('disabled', false);
        $("#slcFormaPagoRef").val(21).trigger('change.select2');
        $("#slcFormaPagoRef").prop("disabled", true);
    }else{
        $("#slcFormaPagoRef").val(idformapago).trigger('change.select2');
    }
});

document.addEventListener('input', function (e) {
    if(e.target.classList.contains('uppercase')){
        e.target.value = e.target.value.toUpperCase();
    }
});

function validarRazonSocialRef(razon_social){
    if(razon_social == ""){
        $("#divNuevaRazonSocialRef").show();
        $("#slcRazonSocialRef").removeClass("requerido");
        $(".nuevaRazonSocialRef").addClass("requerido");
    }else{
        $("#divNuevaRazonSocialRef").hide();
        $("#slcRazonSocialRef").addClass("requerido");
        $(".nuevaRazonSocialRef").removeClass("requerido");
    }
}

function validarFormRefacturar(){
    var correos = $("#txtCorreoRef").val().split(",");
    var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    for(var i = 0; i < correos.length; i++){
        if(!regexCorreo.test(correos[i].trim())){
            swalFocus("Error", "El correo electrónico '"+correos[i].trim()+"' no es válido", "error", "txtCorreoRef");
            return;
        }
    }
    var correosAdicionales = $("#txtCorreoAdicionalRef").val().trim();
    if(correosAdicionales !== ""){
        var adicionales = correosAdicionales.split(",");
        for(var i = 0; i < adicionales.length; i++){
            if(!regexCorreo.test(adicionales[i].trim())){
                swalFocus("Error", "El correo adicional '"+adicionales[i].trim()+"' no es válido", "error", "txtCorreoAdicionalRef");
                return;
            }
        }
    }
    validarFormulario('formRefacturar');
}

function validarMetodoPagoRef(){
    if($("#slcMetodoPagoRef").val() == 1){ // PPD
        $('#slcFormaPagoRef option[value="21"]').prop('disabled', false);
        $("#slcFormaPagoRef").val(21).trigger('change.select2');
        $("#slcFormaPagoRef").prop("disabled", true);
    }else{
        $('#slcFormaPagoRef option[value="21"]').prop('disabled', true);
        $("#slcFormaPagoRef").val(0).trigger('change.select2');
        $("#slcFormaPagoRef").prop("disabled", false);
    }
}
</script>
