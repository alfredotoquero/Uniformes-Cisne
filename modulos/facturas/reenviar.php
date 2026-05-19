<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$f = new Facturas();
$c = new Clientes();

$factura = $f->getFactura(["idfactura" => $_GET["idfactura"]])["factura"];

$nombreCliente = "";
if ($factura["idcliente"] > 0) {
    $clienteData = $c->obtenerCliente(["idcliente" => $factura["idcliente"]])["cliente"];
    $partes = array_filter([$clienteData["correo"], $clienteData["correos_adicionales"]]);
    $correoPrecargado = implode(",", $partes);
    $nombreCliente = $clienteData["nombre"];
} else {
    $correoPrecargado = "";
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div id="divReenviar" style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Reenviar factura <?= $factura["serie"] . "-" . $factura["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formReenviar" name="formReenviar">
        <input type="hidden" name="controlador" id="controlador" value="facturas">
        <input type="hidden" name="accion" id="accion" value="reenviar">
        <input type="hidden" name="idusuario" value="<?= $_SESSION["usuario"]["idusuario"] ?>">
        <input type="hidden" name="idfactura" value="<?= (int)$_GET["idfactura"] ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">
        <?php if (!empty($nombreCliente)) { ?>
        <div class="mb-3">
            <label class="form-label">Cliente</label><br>
            <?= $nombreCliente ?>
        </div>
        <?php } ?>
        <div class="row mb-3">
            <div class="col-6">
                <label class="form-label">Total</label><br>
                $<?= number_format($factura["total"], 2) ?>
            </div>
            <div class="col-6">
                <label class="form-label">Fecha</label><br>
                <?= str_replace("<br>", " ", fecha_formateada($factura["registro"])) ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="txtCorreoReenviar" class="form-label">Correo electrónico<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreoReenviar"
                placeholder="Ingresa el correo electrónico" autocomplete="off"
                data-mensajeerror="Debes indicar el correo electrónico"
                value="<?= htmlspecialchars($correoPrecargado) ?>"
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*"
                title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Para enviar a múltiples destinatarios, separa los correos con coma (ej: correo1@ejemplo.com, correo2@ejemplo.com)</small>
        </div>
        <div class="mb-3">
            <label for="txtCorreoAdicionalReenviar" class="form-label">Correos adicionales <small class="text-muted">(opcional)</small></label>
            <input type="text" class="form-control" name="txtCorreoAdicional" id="txtCorreoAdicionalReenviar"
                placeholder="Ingresa correos adicionales" autocomplete="off"
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*"
                title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Correos extra a los que se enviará la factura, separados por coma. No se guardarán en el sistema.</small>
        </div>
        <button type="button" onclick="validarFormReenviar();" class="btn btn-primary">Reenviar</button>
    </form>
</div>
<script>
function validarFormReenviar() {
    var correos = $("#txtCorreoReenviar").val().split(",");
    var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    for (var i = 0; i < correos.length; i++) {
        if (!regexCorreo.test(correos[i].trim())) {
            swalFocus("Error", "El correo electrónico '" + correos[i].trim() + "' no es válido", "error", "txtCorreoReenviar");
            return;
        }
    }
    var correosAdicionales = $("#txtCorreoAdicionalReenviar").val().trim();
    if (correosAdicionales !== "") {
        var adicionales = correosAdicionales.split(",");
        for (var i = 0; i < adicionales.length; i++) {
            if (!regexCorreo.test(adicionales[i].trim())) {
                swalFocus("Error", "El correo adicional '" + adicionales[i].trim() + "' no es válido", "error", "txtCorreoAdicionalReenviar");
                return;
            }
        }
    }
    validarFormulario('formReenviar');
}
</script>
