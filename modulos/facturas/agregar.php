<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$f = new Facturas();

if(!$f->puedeEmitirDirectas($_SESSION["usuario"]["idusuario"])){
    ?>
    <div style="width:500px;">
        <div class="alert alert-warning mb-0">No tienes permiso para emitir facturas directas.</div>
    </div>
    <?php
    exit;
}

$sat = new SAT();
$c   = new Clientes();
$e   = new Emisores();
$t   = new Tiendas();

$clientes          = $c->obtenerClientes(array("evitar_paginacion" => 1));
$tiendas           = $t->obtenerTiendas();
$emisores          = $e->obtenerEmisores(array())["emisores"];
$regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];
$usoscfdi          = $sat->obtenerUsosCFDI()["usoscfdi"];
$metodospago       = $sat->obtenerMetodosPago()["metodospago"];
$formaspago        = $sat->obtenerFormasPago()["formaspago"];
$tasasiva          = $f->obtenerTasasIVA();

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div id="divFacturaDirecta" style="width:1000px; max-width:100%;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Nueva factura directa</h4>
        </div>
    </div>
    <hr>
    <form id="formFacturaDirecta" name="formFacturaDirecta">
        <input type="hidden" name="controlador" id="controlador" value="facturas">
        <input type="hidden" name="accion" id="accion" value="facturarDirecta">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">

        <!-- Paso 1: Conceptos -->
        <div id="stepConceptosFD">
            <p class="text-muted mb-2">Captura los conceptos a facturar. Los precios son unitarios <strong>sin IVA</strong>.</p>
            <div id="divConceptosFD" class="mb-2"></div>
            <button type="button" class="btn btn-light btn-sm mb-3" onclick="agregarConceptoFD();"><i class="uil uil-plus me-1"></i>Agregar concepto</button>

            <div class="row justify-content-end">
                <div class="col-12 col-md-5">
                    <div class="mb-2">
                        <label for="slcTasaIVAFD" class="form-label">Tasa de IVA<span>*</span></label>
                        <select class="form-control" name="slcTasaIVA" id="slcTasaIVAFD" onchange="calcularTotalesFD();">
                            <?php foreach($tasasiva as $clave => $descripcion): ?>
                                <option value="<?= $clave ?>"><?= $descripcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <table class="table table-sm mb-3">
                        <tr><td>Subtotal</td><td class="text-end" id="tdSubtotalFD">$0.00</td></tr>
                        <tr><td>IVA</td><td class="text-end" id="tdIVAFD">$0.00</td></tr>
                        <tr class="fw-bold"><td>Total</td><td class="text-end" id="tdTotalFD">$0.00</td></tr>
                    </table>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-primary" onclick="siguientePasoFD();">
                    Siguiente <i class="uil uil-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Paso 2: Datos fiscales -->
        <div id="stepDatosFD" style="display:none;">
            <!-- Las secciones van a todo lo ancho y los campos se reparten dentro de cada una,
                 para que la pantalla quede pareja con y sin captura de razón social -->
            <h5 class="text-uppercase text-muted small fw-bold border-bottom pb-1 mb-2">Receptor</h5>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="slcClienteFD" class="form-label">Cliente</label>
                    <select class="select2fd" name="slcCliente" id="slcClienteFD" onchange="cambiarClienteFD(this.value);">
                        <option value="0">Sin cliente (capturar datos fiscales)</option>
                        <?php
                        if($clientes["respuesta"] == "OK"){
                            while($cliente = mysqli_fetch_assoc($clientes["clientes"])){
                                ?>
                                <option value="<?= $cliente["idcliente"] ?>"><?= htmlspecialchars($cliente["nombre"]) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3" id="divRazonSocialFD" style="display:none;">
                    <label for="slcRazonSocialFD" class="form-label">Razón social<span>*</span></label>
                    <select class="form-control" name="slcRazonSocial" id="slcRazonSocialFD" data-mensajeerror="Debes indicar una razón social" onchange="cambiarRazonSocialFD(this.value);"></select>
                </div>
            </div>
            <div id="divNuevaRazonSocialFD">
                <div class="row">
                    <div class="col-12 col-md-8 mb-3">
                        <label for="txtRazonSocialFD" class="form-label">Razón social<span>*</span></label>
                        <input type="text" class="form-control uppercase nuevaRazonSocialFD requerido" name="txtRazonSocial" id="txtRazonSocialFD" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social">
                    </div>
                    <div class="col-8 col-md-4 mb-3">
                        <label for="txtRFCFD" class="form-label">RFC<span>*</span></label>
                        <input type="text" class="form-control uppercase nuevaRazonSocialFD requerido" name="txtRFC" id="txtRFCFD" placeholder="Ingresa el RFC" autocomplete="off" maxlength="13" data-mensajeerror="Debes indicar el RFC">
                    </div>
                    <div class="col-4 col-md-2 mb-3">
                        <label for="txtCodigoPostalFD" class="form-label">C.P.<span>*</span></label>
                        <input type="text" class="form-control nuevaRazonSocialFD requerido" name="txtCodigoPostal" id="txtCodigoPostalFD" placeholder="00000" autocomplete="off" maxlength="5" data-mensajeerror="Debes indicar el código postal">
                    </div>
                    <div class="col-12 col-md-5 mb-3">
                        <label for="slcRegimenFiscalFD" class="form-label">Régimen fiscal<span>*</span></label>
                        <select class="nuevaRazonSocialFD requerido select2fd" name="slcRegimenFiscal" id="slcRegimenFiscalFD" data-mensajeerror="Debes indicar el régimen fiscal">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach($regimenesfiscales as $regimenfiscal): ?>
                                <option value="<?= $regimenfiscal["idregimenfiscal"] ?>"><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-5 mb-3">
                        <label for="slcUsoCFDIFD" class="form-label">Uso del CFDI<span>*</span></label>
                        <select class="nuevaRazonSocialFD requerido select2fd" name="slcUsoCFDI" id="slcUsoCFDIFD" data-mensajeerror="Debes indicar el uso del CFDI">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach($usoscfdi as $usocfdi): ?>
                                <option value="<?= $usocfdi["idusocfdi"] ?>"><?= $usocfdi["usocfdi"]." - ".$usocfdi["descripcion"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <h5 class="text-uppercase text-muted small fw-bold border-bottom pb-1 mb-2 mt-1">Pago y emisión</h5>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="slcMetodoPagoFD" class="form-label">Método de pago<span>*</span></label>
                    <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPagoFD" onchange="validarMetodoPagoFD();" data-mensajeerror="Debes indicar el método de pago">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach($metodospago as $metodopago): ?>
                            <option value="<?= $metodopago["idmetodopago"] ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="slcFormaPagoFD" class="form-label">Forma de pago<span>*</span></label>
                    <select class="requerido select2fd" name="slcFormaPago" id="slcFormaPagoFD" data-mensajeerror="Debes indicar la forma de pago">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach($formaspago as $formapago): ?>
                            <option value="<?= $formapago["idformapago"] ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="slcEmisorFD" class="form-label">Emisor<span>*</span></label>
                    <select class="requerido select2fd" name="slcEmisor" id="slcEmisorFD" data-mensajeerror="Debes indicar un emisor">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach($emisores as $emisor): ?>
                            <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"]." - ".$emisor["rfc"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="slcTiendaFD" class="form-label">Tienda<span>*</span></label>
                    <select class="requerido select2fd" name="slcTienda" id="slcTiendaFD" data-mensajeerror="Debes indicar la tienda">
                        <option value="0">--Seleccionar--</option>
                        <?php
                        if($tiendas["respuesta"] == "OK"){
                            while($tienda = mysqli_fetch_assoc($tiendas["tiendas"])){
                                ?>
                                <option value="<?= $tienda["idtienda"] ?>"><?= htmlspecialchars($tienda["nombre"]) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <small class="text-muted d-block mt-1">De la tienda salen el logo del PDF y la cuenta desde la que se envía el correo.</small>
                </div>
            </div>

            <h5 class="text-uppercase text-muted small fw-bold border-bottom pb-1 mb-2 mt-1">Envío</h5>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="txtCorreoFD" class="form-label">Correo electrónico<span>*</span></label>
                    <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreoFD" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar el correo electrónico">
                    <small class="text-muted d-block mt-1">Para varios destinatarios, sepáralos con coma.</small>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="txtCorreoAdicionalFD" class="form-label">Correos adicionales <small class="text-muted">(opcional)</small></label>
                    <input type="text" class="form-control" name="txtCorreoAdicional" id="txtCorreoAdicionalFD" placeholder="Ingresa correos adicionales" autocomplete="off">
                    <small class="text-muted d-block mt-1">Se envían una sola vez; no se guardan en el cliente.</small>
                </div>
                <div class="col-12 mb-3">
                    <label for="txtComentariosFD" class="form-label">Comentarios <small class="text-muted">(opcional)</small></label>
                    <textarea class="form-control" name="txtComentarios" id="txtComentariosFD" rows="2" placeholder="Se imprimen en el PDF de la factura"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="anteriorPasoFD();">
                    <i class="uil uil-arrow-left"></i> Anterior
                </button>
                <button type="button" onclick="validarFormFacturaDirecta();" class="btn btn-primary">Facturar</button>
            </div>
        </div>
    </form>
</div>
<script>
var indiceConceptoFD = 0;

$(document).ready(function () {
    // .fancybox-content trae overflow:auto, así que recorta los desplegables de select2
    // que se abren cerca del borde inferior (el de tienda quedaba oculto). Con overflow
    // visible el desplegable sale del recuadro y la ventana del fancy se desplaza si hace
    // falta; los desplegables siguen dentro de #divFacturaDirecta para no pelear con el
    // control de foco del fancybox.
    $("#divFacturaDirecta").closest(".fancybox-content").css("overflow", "visible");

    $(".select2fd", "#formFacturaDirecta").select2({
        dropdownParent: $('#divFacturaDirecta'),
        width: '100%'
    });

    $("#slcFormaPagoFD").val(0).trigger('change.select2').prop("disabled", true);

    $("#formFacturaDirecta").on("input", ".uppercase", function () {
        this.value = this.value.toUpperCase();
    });

    $("#divConceptosFD").on("input", ".inputCantidadFD, .inputPrecioFD", function () {
        calcularTotalesFD();
    });

    agregarConceptoFD();
});

// Cada concepto ocupa dos líneas: arriba las claves del SAT y la cantidad, abajo la
// descripción y los importes. Las columnas coinciden entre líneas (7/3/2) para que se lean
// alineadas, y la clave de producto tiene espacio para su descripción completa.
function agregarConceptoFD(){
    var i = indiceConceptoFD++;
    var concepto = $(
        '<div class="conceptoFD border rounded px-2 pt-1 pb-2 mb-2" data-indice="' + i + '">' +
            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                '<span class="small fw-bold text-muted numConceptoFD"></span>' +
                '<a href="javascript:;" class="text-danger" onclick="eliminarConceptoFD(this);" title="Eliminar concepto"><i class="uil uil-trash-alt"></i></a>' +
            '</div>' +
            '<div class="row g-2">' +
                '<div class="col-12 col-md-7">' +
                    '<label class="form-label small mb-0">Clave producto/servicio SAT</label>' +
                    '<select class="slcProdServFD" name="conceptos[' + i + '][claveprodserv]"></select>' +
                '</div>' +
                '<div class="col-8 col-md-3">' +
                    '<label class="form-label small mb-0">Clave unidad</label>' +
                    '<select class="slcUnidadFD" name="conceptos[' + i + '][claveunidad]"></select>' +
                '</div>' +
                '<div class="col-4 col-md-2">' +
                    '<label class="form-label small mb-0">Cantidad</label>' +
                    '<input type="number" class="form-control inputCantidadFD" name="conceptos[' + i + '][cantidad]" value="1" min="0.01" step="0.01">' +
                '</div>' +
                '<div class="col-12 col-md-7">' +
                    '<label class="form-label small mb-0">Descripción</label>' +
                    '<input type="text" class="form-control inputDescripcionFD" name="conceptos[' + i + '][descripcion]" maxlength="1000" autocomplete="off">' +
                '</div>' +
                '<div class="col-6 col-md-3">' +
                    '<label class="form-label small mb-0">Precio unitario (sin IVA)</label>' +
                    '<input type="number" class="form-control inputPrecioFD" name="conceptos[' + i + '][valorunitario]" min="0.01" step="0.01">' +
                '</div>' +
                '<div class="col-6 col-md-2">' +
                    '<label class="form-label small mb-0">Importe</label>' +
                    '<div class="form-control bg-light text-end importeFD">$0.00</div>' +
                '</div>' +
            '</div>' +
        '</div>'
    );

    $("#divConceptosFD").append(concepto);
    numerarConceptosFD();

    concepto.find(".slcProdServFD").select2({
        dropdownParent: $('#divFacturaDirecta'),
        width: '100%',
        placeholder: "Buscar clave",
        minimumInputLength: 4,
        ajax: {
            url: "/assets/php/controladores/sat.php",
            type: "POST",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { palabraClave: params.term, accion: "obtenerProductosServicios" };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.productosservicios || {}, function (obj) {
                        return { id: obj.clave, text: obj.clave + ' - ' + obj.descripcion };
                    })
                };
            },
            cache: true
        }
    });

    concepto.find(".slcUnidadFD").select2({
        dropdownParent: $('#divFacturaDirecta'),
        width: '100%',
        placeholder: "Buscar unidad",
        minimumInputLength: 1,
        ajax: {
            url: "/assets/php/controladores/sat.php",
            type: "POST",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { palabraClave: params.term, accion: "obtenerUnidadesMedida" };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.unidadesmedida || {}, function (obj) {
                        return { id: obj.clave, text: obj.clave + ' - ' + obj.nombre };
                    })
                };
            },
            cache: true
        }
    });
}

function eliminarConceptoFD(elemento){
    if($("#divConceptosFD .conceptoFD").length <= 1){
        Swal.fire("Atención", "La factura debe tener al menos un concepto.", "warning");
        return;
    }
    $(elemento).closest(".conceptoFD").remove();
    numerarConceptosFD();
    calcularTotalesFD();
}

function numerarConceptosFD(){
    $("#divConceptosFD .conceptoFD").each(function (n) {
        $(this).find(".numConceptoFD").text("Concepto " + (n + 1));
    });
}

// Redondeo a centavos igual que en el servidor: cada importe se redondea por separado y
// el subtotal es la suma de los importes ya redondeados
function redondearFD(valor){
    return Math.round((valor + Number.EPSILON) * 100) / 100;
}

function calcularTotalesFD(){
    var subtotal = 0;
    $("#divConceptosFD .conceptoFD").each(function () {
        var cantidad = redondearFD(parseFloat($(this).find(".inputCantidadFD").val()) || 0);
        var precio = redondearFD(parseFloat($(this).find(".inputPrecioFD").val()) || 0);
        var importe = redondearFD(cantidad * precio);
        $(this).find(".importeFD").text("$" + formatMoney(importe, 2, ".", ","));
        subtotal = redondearFD(subtotal + importe);
    });

    var tasa = $("#slcTasaIVAFD").val();
    var porcentaje = (tasa == "exento") ? 0 : parseFloat(tasa);
    var iva = redondearFD(subtotal * porcentaje / 100);

    $("#tdSubtotalFD").text("$" + formatMoney(subtotal, 2, ".", ","));
    $("#tdIVAFD").text("$" + formatMoney(iva, 2, ".", ","));
    $("#tdTotalFD").text("$" + formatMoney(redondearFD(subtotal + iva), 2, ".", ","));
}

function siguientePasoFD(){
    var error = "";
    $("#divConceptosFD .conceptoFD").each(function (n) {
        var renglon = n + 1;
        if(!(parseFloat($(this).find(".inputCantidadFD").val()) > 0)){
            error = "La cantidad del concepto " + renglon + " debe ser mayor a cero.";
        }else if(!$(this).find(".slcProdServFD").val()){
            error = "Debes indicar la clave de producto o servicio del concepto " + renglon + ".";
        }else if(!$(this).find(".slcUnidadFD").val()){
            error = "Debes indicar la clave de unidad del concepto " + renglon + ".";
        }else if($.trim($(this).find(".inputDescripcionFD").val()) == ""){
            error = "Debes indicar la descripción del concepto " + renglon + ".";
        }else if(!(parseFloat($(this).find(".inputPrecioFD").val()) > 0)){
            error = "El precio unitario del concepto " + renglon + " debe ser mayor a cero.";
        }
        if(error != ""){
            return false;
        }
    });

    if(error != ""){
        Swal.fire("Error", error, "error");
        return;
    }

    $("#stepConceptosFD").hide();
    $("#stepDatosFD").show();
}

function anteriorPasoFD(){
    $("#stepDatosFD").hide();
    $("#stepConceptosFD").show();
}

function mostrarNuevaRazonSocialFD(mostrar){
    if(mostrar){
        $("#divNuevaRazonSocialFD").show();
        $(".nuevaRazonSocialFD").addClass("requerido");
    }else{
        $("#divNuevaRazonSocialFD").hide();
        $(".nuevaRazonSocialFD").removeClass("requerido");
    }
}

function cambiarClienteFD(idcliente){
    var $razon = $("#slcRazonSocialFD");
    $razon.empty();

    if(idcliente == 0){
        $("#divRazonSocialFD").hide();
        $razon.removeClass("requerido");
        mostrarNuevaRazonSocialFD(true);
        return;
    }

    $.ajax({
        type: "POST",
        url: "/assets/php/controladores/facturas.php",
        data: { accion: "datosClienteFacturacion", idcliente: idcliente },
        dataType: "json",
        success: function (data) {
            if(data.respuesta != "OK"){
                Swal.fire("Error", data.mensaje, "error");
                return;
            }

            $razon.append(new Option("--Seleccionar--", "0"));
            $.each(data.razones || {}, function (k, razon) {
                $razon.append(new Option(razon.razon_social + " - " + razon.rfc, razon.idrazonsocial));
            });
            $razon.append(new Option("Nueva razón social", ""));

            // Con una sola razón social se preselecciona; sin ninguna se pasa directo a
            // capturar una nueva
            var razones = $.map(data.razones || {}, function (r) { return r; });
            if(razones.length == 1){
                $razon.val(razones[0].idrazonsocial);
            }else if(razones.length == 0){
                $razon.val("");
            }

            $("#divRazonSocialFD").show();
            cambiarRazonSocialFD($razon.val());

            if(data.correo){
                $("#txtCorreoFD").val(data.correo);
            }
        }
    });
}

function cambiarRazonSocialFD(valor){
    // "" es "Nueva razón social". validarFormulario trata "" como 0 en los select, así que
    // en ese caso se le quita requerido al select y se lo pasa a los campos de captura
    if(valor === ""){
        $("#slcRazonSocialFD").removeClass("requerido");
        mostrarNuevaRazonSocialFD(true);
    }else{
        $("#slcRazonSocialFD").addClass("requerido");
        mostrarNuevaRazonSocialFD(false);
    }
}

function validarMetodoPagoFD(){
    if($("#slcMetodoPagoFD").val() == 1){ // PPD
        $('#slcFormaPagoFD option[value="21"]').prop('disabled', false);
        $("#slcFormaPagoFD").val(21).trigger('change.select2');
        $("#slcFormaPagoFD").prop("disabled", true);
    }else{
        $('#slcFormaPagoFD option[value="21"]').prop('disabled', true);
        $("#slcFormaPagoFD").val(0).trigger('change.select2');
        $("#slcFormaPagoFD").prop("disabled", false);
    }
}

function validarFormFacturaDirecta(){
    var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

    // Los requeridos se revisan aquí y no con validarFormulario porque la confirmación del
    // total tiene que ir después de validar y antes de enviar
    var faltante = null;
    $(".requerido", "#formFacturaDirecta").each(function () {
        var vacio = ($(this).is("select")) ? ($(this).val() == 0 || $(this).val() == null) : ($.trim($(this).val()) == "");
        if(vacio){
            faltante = $(this);
            return false;
        }
    });
    if(faltante){
        swalFocus("Error", faltante.data("mensajeerror"), "error", faltante.attr("id"));
        return;
    }

    if($("#divNuevaRazonSocialFD").is(":visible")){
        var rfc = $.trim($("#txtRFCFD").val());
        if(rfc != "" && !/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/.test(rfc)){
            swalFocus("Error", "El RFC no tiene un formato válido", "error", "txtRFCFD");
            return;
        }
        var cp = $.trim($("#txtCodigoPostalFD").val());
        if(cp != "" && !/^[0-9]{5}$/.test(cp)){
            swalFocus("Error", "El código postal debe tener 5 dígitos", "error", "txtCodigoPostalFD");
            return;
        }
    }

    var correos = $("#txtCorreoFD").val().split(",");
    for(var i = 0; i < correos.length; i++){
        if(!regexCorreo.test(correos[i].trim())){
            swalFocus("Error", "El correo electrónico '" + correos[i].trim() + "' no es válido", "error", "txtCorreoFD");
            return;
        }
    }
    var correosAdicionales = $("#txtCorreoAdicionalFD").val().trim();
    if(correosAdicionales !== ""){
        var adicionales = correosAdicionales.split(",");
        for(var i = 0; i < adicionales.length; i++){
            if(!regexCorreo.test(adicionales[i].trim())){
                swalFocus("Error", "El correo adicional '" + adicionales[i].trim() + "' no es válido", "error", "txtCorreoAdicionalFD");
                return;
            }
        }
    }

    Swal.fire({
        title: "Confirmar factura",
        html: "Se timbrará una factura por <strong>" + $("#tdTotalFD").text() + "</strong>.<br>¿Deseas continuar?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Facturar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if(result.value){
            enviarFormulario('formFacturaDirecta');
        }
    });
}
</script>
