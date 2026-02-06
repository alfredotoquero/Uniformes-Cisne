<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");

$sat = new SAT();
$p = new Pedidos();
$c = new Clientes();
$e = new Emisores();

$pedido = $p->obtenerPedido(array(
    "idpedido" => $_GET["idpedido"]
))["pedido"];

if(!empty($pedido["idtienda"])){
    if($pedido["idcliente"]>0){
        $razonessociales = $c->obtenerRazonesSociales($pedido["idcliente"])["razones"];
    }

    $regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];
    $usoscfdi = $sat->obtenerUsosCFDI()["usoscfdi"];
    $metodospago = $sat->obtenerMetodosPago()["metodospago"];
    $formaspago = $sat->obtenerFormasPago()["formaspago"];

    $emisores = $e->obtenerEmisores(array())["emisores"];

    unset($_SESSION["authToken"]);
    $_SESSION["authToken"]=sha1(uniqid(microtime(), true));
    ?>
    <div id="divFacturar" style="width:500px;">
        <div class="row">
            <div class="col-12">
                <h4 class="header-title">Facturar pedido #<?= $pedido["idpedido"] ?></h4>
            </div>
        </div>
        <hr>
        <form id="formFacturar" name="formFacturar">
            <input type="hidden" name="controlador" id="controlador" value="pedidos">
            <input type="hidden" name="accion" id="accion" value="facturar">
            <input type="hidden" name="idusuario" id="idusuario" value="<?= $_SESSION["usuario"]["idusuario"] ?>">
            <input type="hidden" name="idpedido" id="idpedido" value="<?= $_GET["idpedido"] ?>">
            <input type="hidden" name="idcliente" id="idcliente" value="<?= $pedido["idcliente"] ?>">
            <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
            <div class="mb-3">
                <label class="form-label">Cliente</label><br>
                <?= $pedido["cliente"] ?>
            </div>
            <div id="divRazonSocial">
                <div class="mb-3">
                    <label for="slcRazonSocial" class="form-label">Razón social<span>*</span></label>
                    <select class="form-control requerido" name="slcRazonSocial" id="slcRazonSocial" data-mensajeerror="Debes indicar una razón social" onchange="validarRazonSocial(this.value)">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($razonessociales as $razonsocial) {
                            ?>
                            <option value="<?= $razonsocial["idrazonsocial"]; ?>"><?= $razonsocial["razon_social"]." - ".$razonsocial["rfc"]; ?></option>
                            <?
                        }
                        ?>
                        <option value="">Nueva razón social</option>
                    </select>
                </div>
            </div>
            <div id="divNuevaRazonSocial">
                <div class="mb-3">
                    <label id="lblRazonSocial" for="txtRazonSocial" class="form-label">Razón social<span>*</span></label>
                    <input type="text" class="form-control uppercase nuevaRazonSocial" name="txtRazonSocial" id="txtRazonSocial" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social">
                </div>
                <div class="mb-3">
                    <label for="txtRFC" class="form-label">RFC<span>*</span></label>
                    <input type="text" class="form-control uppercase nuevaRazonSocial" name="txtRFC" id="txtRFC" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar el RFC">
                </div>
                <div class="mb-3">
                    <label for="txtCodigoPostal" class="form-label">Código postal<span>*</span></label>
                    <input type="text" class="form-control nuevaRazonSocial" name="txtCodigoPostal" id="txtCodigoPostal" placeholder="Ingresa el código postal" autocomplete="off" data-mensajeerror="Debes indicar el código postal">
                </div>
                <div class="mb-3">
                    <label for="slcRegimenFiscal" class="form-label">Régimen fiscal<span>*</span></label>
                    <select class="nuevaRazonSocial select2" name="slcRegimenFiscal" id="slcRegimenFiscal" data-mensajeerror="Debes indicar el régimen fiscal">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($regimenesfiscales as $regimenfiscal) {
                            ?>
                            <option value="<?= $regimenfiscal["idregimenfiscal"]; ?>"><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"]; ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="slcUsoCFDI" class="form-label">Uso del CFDI<span>*</span></label>
                    <select class="nuevaRazonSocial select2" name="slcUsoCFDI" id="slcUsoCFDI" data-mensajeerror="Debes indicar el uso del CFDI">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($usoscfdi as $usocfdi) {
                            ?>
                            <option value="<?= $usocfdi["idusocfdi"]; ?>"><?= $usocfdi["usocfdi"]." - ".$usocfdi["descripcion"]; ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="txtCorreo" class="form-label">Correo electrónico<span>*</span></label>
                <input type="email" class="form-control requerido" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar el correo electrónico" value="<?= $pedido["correocliente"] ?>">
            </div>
            <div class="mb-3">
                <label for="slcMetodoPago" class="form-label">Método de pago<span>*</span></label>
                <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPago" onchange="validarMetodoPago();" data-mensajeerror="Debes indicar el método de pago">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($metodospago as $metodopago) {
                        ?>
                        <option value="<?= $metodopago["idmetodopago"]; ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="slcFormaPago" class="form-label">Forma de pago<span>*</span></label>
                <select class="requerido select2" name="slcFormaPago" id="slcFormaPago" data-mensajeerror="Debes indicar la forma de pago">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($formaspago as $formapago) {
                        ?>
                        <option value="<?= $formapago["idformapago"]; ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="slcEmisor" class="form-label">Emisor<span>*</span></label>
                <select class="requerido select2" name="slcEmisor" id="slcEmisor" data-mensajeerror="Debes indicar un emisor">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($emisores as $emisor) {
                        ?>
                        <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"]." - ".$emisor["rfc"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <button type="button" onclick="validarFormulario('formFacturar');" class="btn btn-primary">Facturar</button>
        </form>
    </div>
    <script>
    $(document).ready(function () {
        $(".select2","#formFacturar").select2({
            dropdownParent: $('#divFacturar'),
            width: '100%'
        });

        <? if($pedido["idcliente"]>0){ ?>
        $("#divRazonSocial").show();
        $("#divNuevaRazonSocial").hide();
        <? }else{ ?>
        $("#divRazonSocial").hide();
        $("#divNuevaRazonSocial").show();
        $("#slcRazonSocial").removeClass("requerido");
        $(".nuevaRazonSocial").addClass("requerido");
        <? } ?>
        $("#slcFormaPago").val(0).trigger('change.select2').prop("disabled", true);
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('uppercase')) {
            e.target.value = e.target.value.toUpperCase();
        }
    });

    function validarRazonSocial(razon_social){
        if(razon_social==""){
            $("#divNuevaRazonSocial").show();
            $("#slcRazonSocial").removeClass("requerido");
            $(".nuevaRazonSocial").addClass("requerido");
        }else{
            $("#divNuevaRazonSocial").hide();
            $("#slcRazonSocial").addClass("requerido");
            $(".nuevaRazonSocial").removeClass("requerido");
        }
    }

    function validarMetodoPago(){
        if($("#slcMetodoPago").val() == 1){ //PPD
            $('#slcFormaPago option[value="21"]').prop('disabled', false);
            $("#slcFormaPago").val(21).trigger('change.select2');
            $("#slcFormaPago").prop("disabled", true);
        }else{
            $('#slcFormaPago option[value="21"]').prop('disabled', true);
            $("#slcFormaPago").val(0).trigger('change.select2');
            $("#slcFormaPago").prop("disabled", false);
        }
    }
    </script>
<?
}else{
?>
<script>
    $.fancybox.close();
    Swal.fire("Atención", "Debes asignar una tienda a la sucursal <?= $pedido["sucursal"] ?>", "warning");
</script>
<?
}
?>