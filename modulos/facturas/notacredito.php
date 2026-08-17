<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/NotasCredito.php");

$claseSAT = new SAT();
$claseFacturas = new Facturas();
$claseNotasCredito = new NotasCredito();

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

$factura = $claseFacturas->getFactura(array("idfactura" => $idfactura));

$tiposrelacion = $claseNotasCredito->obtenerTiposRelacion();
$metodospago = $claseSAT->obtenerMetodosPago()["metodospago"];
$formaspago = $claseSAT->obtenerFormasPago()["formaspago"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <?php
    if($factura["respuesta"]=="OK"){
        $factura = $factura["factura"];

        // La tasa se toma de la factura para que la nota de crédito la refleje igual
        $tasaiva = ($factura["subtotal"] > 0) ? round(($factura["iva"] / $factura["subtotal"]) * 100) : 0;
        $saldo = round($factura["saldo"],2);
    ?>
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Generar nota de crédito (Factura <?= $factura["serie"]."-".$factura["folio"] ?>)</h4>
        </div>
    </div>
    <hr>
    <?php if($factura["status"]!=1 || $saldo<=0){ ?>
        <div class="alert alert-warning">
            <?= ($factura["status"]!=1) ? "Solo se pueden generar notas de crédito de facturas activas." : "La factura no tiene saldo pendiente." ?>
        </div>
    <?php }else{ ?>
    <form id="formNotaCredito" name="formNotaCredito">
        <input type="hidden" name="controlador" id="controlador" value="notascredito">
        <input type="hidden" name="accion" id="accion" value="crear">
        <input type="hidden" name="idfactura" value="<?= $idfactura ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">
        <input type="hidden" id="tasaiva" value="<?= $tasaiva ?>">
        <input type="hidden" id="saldo" value="<?= $saldo ?>">

        <div class="mb-3">
            <label class="form-label">Cliente</label><br>
            <?= $factura["cliente"] ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Saldo de la factura</label><br>
            $<?= number_format($saldo,2) ?>
        </div>
        <div class="mb-3">
            <label for="slcTipoRelacion" class="form-label">Tipo de relación<span>*</span></label>
            <select class="form-control requerido" name="slcTipoRelacion" id="slcTipoRelacion" data-mensajeerror="Debes indicar el tipo de relación" onchange="cambiarTipoRelacion();">
                <option value="0">--Seleccionar--</option>
                <?php foreach($tiposrelacion as $clave => $descripcion){ ?>
                    <option value="<?= $clave ?>"><?= $clave." - ".$descripcion ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcMetodoPago" class="form-label">Método de pago<span>*</span></label>
            <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPago" data-mensajeerror="Debes indicar el método de pago">
                <option value="0">--Seleccionar--</option>
                <?php foreach($metodospago as $metodopago){ ?>
                    <option value="<?= $metodopago["idmetodopago"] ?>" data-clave="<?= $metodopago["metodopago"] ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"] ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcFormaPago" class="form-label">Forma de pago<span>*</span></label>
            <select class="form-control requerido" name="slcFormaPago" id="slcFormaPago" data-mensajeerror="Debes indicar la forma de pago">
                <option value="0">--Seleccionar--</option>
                <?php foreach($formaspago as $formapago){ ?>
                    <option value="<?= $formapago["idformapago"] ?>" data-clave="<?= $formapago["formapago"] ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"] ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3" id="divCantidad">
            <label for="txtCantidad" class="form-label">Cantidad<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCantidad" id="txtCantidad" value="1" autocomplete="off" data-mensajeerror="Debes indicar la cantidad" onkeyup="calcularTotales();" onchange="calcularTotales();">
        </div>
        <div class="mb-3">
            <label for="txtDescripcion" class="form-label">Descripción<span>*</span></label>
            <textarea class="form-control requerido" name="txtDescripcion" id="txtDescripcion" rows="3" style="resize:none;" data-mensajeerror="Debes indicar la descripción"></textarea>
        </div>
        <div class="mb-3">
            <label for="txtMonto" class="form-label" id="lblMonto">Precio unitario (sin IVA)<span>*</span></label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control requerido decimales" name="txtMonto" id="txtMonto" autocomplete="off" data-mensajeerror="Debes indicar el importe de la nota de crédito" onkeyup="calcularTotales();" onchange="calcularTotales();">
            </div>
        </div>
        <div class="mb-3 bg-light p-3 rounded">
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong>$<span id="lblSubtotal">0.00</span></strong></div>
            <div class="d-flex justify-content-between"><span>IVA (<?= $tasaiva ?>%)</span><strong>$<span id="lblIVA">0.00</span></strong></div>
            <div class="d-flex justify-content-between"><span>Total nota de crédito</span><strong>$<span id="lblTotal">0.00</span></strong></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between"><span>Saldo de la factura después</span><strong>$<span id="lblSaldoNuevo"><?= number_format($saldo,2) ?></span></strong></div>
        </div>
        <div id="divAlertaSaldo" class="alert alert-danger" style="display:none;">
            El total de la nota de crédito no puede ser mayor al saldo de la factura.
        </div>
        <button type="button" id="btnGenerar" onclick="validarNotaCredito();" class="btn btn-primary">Generar nota de crédito</button>
    </form>
    <?php } ?>
    <?php
    }else{
    ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger"><?= $factura["mensaje"] ?></div>
        </div>
    </div>
    <?php
    }
    ?>
</div>
<script>
$(document).ready(function () {
    // Una nota de crédito no se cobra: por defecto se emite PUE / Por definir, y el
    // anticipo con Compensación, que es lo que el SAT espera en cada caso
    seleccionarPorClave("#slcMetodoPago", "PUE");
    seleccionarPorClave("#slcFormaPago", "21");
    calcularTotales();
});

function seleccionarPorClave(selector, clave) {
    $(selector + " option").each(function () {
        if ($(this).data("clave") == clave) {
            $(selector).val($(this).val());
            return false;
        }
    });
}

function cambiarTipoRelacion() {
    var tipo = $("#slcTipoRelacion").val();

    if (tipo == "07") {
        $("#txtCantidad").val(1);
        $("#divCantidad").hide();
        $("#lblMonto").html('Monto del anticipo (sin IVA)<span>*</span>');
        seleccionarPorClave("#slcFormaPago", "20");
    } else {
        $("#divCantidad").show();
        $("#lblMonto").html('Precio unitario (sin IVA)<span>*</span>');
        seleccionarPorClave("#slcFormaPago", "21");
    }

    calcularTotales();
}

function calcularTotales() {
    var cantidad = parseFloat($("#txtCantidad").val()) || 0;
    var monto = parseFloat($("#txtMonto").val()) || 0;
    var tasaiva = parseFloat($("#tasaiva").val()) || 0;
    var saldo = parseFloat($("#saldo").val()) || 0;

    var subtotal = Math.round(cantidad * monto * 100) / 100;
    var iva = Math.round(subtotal * (tasaiva / 100) * 100) / 100;
    var total = Math.round((subtotal + iva) * 100) / 100;
    var saldonuevo = Math.round((saldo - total) * 100) / 100;

    $("#lblSubtotal").html(subtotal.toFixed(2));
    $("#lblIVA").html(iva.toFixed(2));
    $("#lblTotal").html(total.toFixed(2));
    $("#lblSaldoNuevo").html((saldonuevo < 0 ? 0 : saldonuevo).toFixed(2));

    if (total > saldo + 0.01) {
        $("#divAlertaSaldo").show();
        $("#btnGenerar").prop("disabled", true);
    } else {
        $("#divAlertaSaldo").hide();
        $("#btnGenerar").prop("disabled", false);
    }
}

function validarNotaCredito() {
    var cantidad = parseFloat($("#txtCantidad").val()) || 0;
    var monto = parseFloat($("#txtMonto").val()) || 0;
    var saldo = parseFloat($("#saldo").val()) || 0;
    var total = parseFloat($("#lblTotal").html()) || 0;

    if (cantidad <= 0) {
        Swal.fire("Error", "La cantidad debe ser mayor a cero.", "error");
        return;
    }

    if (monto <= 0) {
        Swal.fire("Error", "El importe de la nota de crédito debe ser mayor a cero.", "error");
        return;
    }

    if (total > saldo + 0.01) {
        Swal.fire("Error", "El total de la nota de crédito no puede ser mayor al saldo de la factura.", "error");
        return;
    }

    Swal.fire({
        title: "Atención",
        text: "Se timbrará una nota de crédito por $" + total.toFixed(2) + " ante el SAT. Esta operación no se puede deshacer desde el sistema.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.value) {
            validarFormulario("formNotaCredito");
        }
    });
}

$(document).on("input", "#txtMonto", function () {
    this.value = this.value.replace(/[^0-9.]/g, "");
});
</script>
