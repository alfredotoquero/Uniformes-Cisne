<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");
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

    $conceptosPendientes = $p->obtenerConceptosPendientesFacturacion($_GET["idpedido"])["conceptos"];

    if (!empty($pedido["correocliente"]) && $pedido["idcliente"] == 0) {
        $correoPrecargado = $pedido["correocliente"];
    } elseif ($pedido["idcliente"] > 0) {
        $clienteData = $c->obtenerCliente(["idcliente" => $pedido["idcliente"]])["cliente"];
        $partes = array_filter([$clienteData["correo"], $clienteData["correos_adicionales"]]);
        $correoPrecargado = implode(",", $partes);
    } else {
        $correoPrecargado = "";
    }

    unset($_SESSION["authToken"]);
    $_SESSION["authToken"] = sha1(uniqid(microtime(), true));
    ?>
    <div id="divFacturar" style="width:650px;">
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
            <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">

            <!-- Paso 1: Selección de partidas -->
            <div id="stepProductos">
                <p class="text-muted mb-2">Selecciona las partidas y cantidades a incluir en esta factura.</p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:36px;">
                                    <input type="checkbox" id="chkTodos" checked title="Seleccionar todos">
                                </th>
                                <th>Producto</th>
                                <th class="text-center" style="width:90px;">Pendiente</th>
                                <th class="text-center" style="width:120px;">A facturar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($conceptosPendientes as $concepto): ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <input type="checkbox"
                                        class="chkConcepto"
                                        data-id="<?= $concepto["idcotizacionproducto"] ?>"
                                        checked>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($concepto["producto"]) ?></td>
                                <td class="text-center align-middle"><?= number_format($concepto["cantidad_pendiente"], 0) ?></td>
                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm inputConcepto text-center"
                                        name="conceptos[<?= $concepto["idcotizacionproducto"] ?>]"
                                        value="<?= (int)$concepto["cantidad_pendiente"] ?>"
                                        min="1"
                                        max="<?= (int)$concepto["cantidad_pendiente"] ?>"
                                        step="1"
                                        data-max="<?= (int)$concepto["cantidad_pendiente"] ?>"
                                        data-id="<?= $concepto["idcotizacionproducto"] ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="siguientePaso()">
                        Siguiente <i class="uil uil-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Paso 2: Datos fiscales -->
            <div id="stepDatos" style="display:none;">
                <div id="divRazonSocial">
                    <div class="mb-3">
                        <label for="slcRazonSocial" class="form-label">Razón social<span>*</span></label>
                        <select class="form-control requerido" name="slcRazonSocial" id="slcRazonSocial" data-mensajeerror="Debes indicar una razón social" onchange="validarRazonSocial(this.value)">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach ($razonessociales as $razonsocial): ?>
                                <option value="<?= $razonsocial["idrazonsocial"] ?>"><?= $razonsocial["razon_social"]." - ".$razonsocial["rfc"] ?></option>
                            <?php endforeach; ?>
                            <option value="">Nueva razón social</option>
                        </select>
                    </div>
                </div>
                <div id="divNuevaRazonSocial">
                    <div class="mb-3">
                        <label for="txtRazonSocial" class="form-label">Razón social<span>*</span></label>
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
                            <?php foreach ($regimenesfiscales as $regimenfiscal): ?>
                                <option value="<?= $regimenfiscal["idregimenfiscal"] ?>"><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="slcUsoCFDI" class="form-label">Uso del CFDI<span>*</span></label>
                        <select class="nuevaRazonSocial select2" name="slcUsoCFDI" id="slcUsoCFDI" data-mensajeerror="Debes indicar el uso del CFDI">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach ($usoscfdi as $usocfdi): ?>
                                <option value="<?= $usocfdi["idusocfdi"] ?>"><?= $usocfdi["usocfdi"]." - ".$usocfdi["descripcion"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="txtCorreo" class="form-label">Correo electrónico<span>*</span></label>
                    <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar el correo electrónico" value="<?= $correoPrecargado ?>" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*" title="Ingresa uno o más correos electrónicos válidos separados por coma">
                    <small class="text-muted d-block mt-1">Para enviar a múltiples destinatarios, separa los correos con coma (ej: correo1@ejemplo.com, correo2@ejemplo.com)</small>
                </div>
                <div class="mb-3">
                    <label for="txtCorreoAdicional" class="form-label">Correos adicionales <small class="text-muted">(opcional)</small></label>
                    <input type="text" class="form-control" name="txtCorreoAdicional" id="txtCorreoAdicional" placeholder="Ingresa correos adicionales" autocomplete="off" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*" title="Ingresa uno o más correos electrónicos válidos separados por coma">
                    <small class="text-muted d-block mt-1">Correos extra a los que se enviará la factura, separados por coma. No se guardarán en el sistema.</small>
                </div>
                <div class="mb-3">
                    <label for="slcMetodoPago" class="form-label">Método de pago<span>*</span></label>
                    <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPago" onchange="validarMetodoPago();" data-mensajeerror="Debes indicar el método de pago">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach ($metodospago as $metodopago): ?>
                            <option value="<?= $metodopago["idmetodopago"] ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="slcFormaPago" class="form-label">Forma de pago<span>*</span></label>
                    <select class="requerido select2" name="slcFormaPago" id="slcFormaPago" data-mensajeerror="Debes indicar la forma de pago">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach ($formaspago as $formapago): ?>
                            <option value="<?= $formapago["idformapago"] ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="slcEmisor" class="form-label">Emisor<span>*</span></label>
                    <select class="requerido select2" name="slcEmisor" id="slcEmisor" data-mensajeerror="Debes indicar un emisor">
                        <option value="0">--Seleccionar--</option>
                        <?php foreach ($emisores as $emisor): ?>
                            <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"]." - ".$emisor["rfc"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="anteriorPaso()">
                        <i class="uil uil-arrow-left"></i> Anterior
                    </button>
                    <button type="button" onclick="validarFormFacturar();" class="btn btn-primary">Facturar</button>
                </div>
            </div>
        </form>
    </div>
    <script>
    $(document).ready(function () {
        $(".select2","#formFacturar").select2({
            dropdownParent: $('#divFacturar'),
            width: '100%'
        });

        <?php if($pedido["idcliente"]>0){ ?>
        $("#divRazonSocial").show();
        $("#divNuevaRazonSocial").hide();
        <?php }else{ ?>
        $("#divRazonSocial").hide();
        $("#divNuevaRazonSocial").show();
        $("#slcRazonSocial").removeClass("requerido");
        $(".nuevaRazonSocial").addClass("requerido");
        <?php } ?>
        $("#slcFormaPago").val(0).trigger('change.select2').prop("disabled", true);

        $("#chkTodos").on("change", function() {
            var checked = $(this).prop("checked");
            $(".chkConcepto").prop("checked", checked).trigger("change");
        });

        $(".chkConcepto").on("change", function() {
            var id = $(this).data("id");
            var input = $(".inputConcepto[data-id='" + id + "']");
            if ($(this).prop("checked")) {
                input.prop("disabled", false).val(input.data("max"));
            } else {
                input.prop("disabled", true).val(0);
            }
        });
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('uppercase')) {
            e.target.value = e.target.value.toUpperCase();
        }
    });

    function siguientePaso() {
        var alguno = false;
        $(".inputConcepto:not(:disabled)").each(function() {
            if (parseFloat($(this).val()) > 0) alguno = true;
        });
        if (!alguno) {
            Swal.fire("Error", "Debes seleccionar al menos una partida con cantidad mayor a cero.", "error");
            return;
        }
        var valido = true;
        $(".inputConcepto:not(:disabled)").each(function() {
            var val = parseFloat($(this).val());
            var max = parseFloat($(this).data("max"));
            if (isNaN(val) || val <= 0 || val > max) {
                Swal.fire("Error", "Verifica las cantidades ingresadas. Ninguna puede exceder la cantidad pendiente.", "error");
                valido = false;
                return false;
            }
        });
        if (!valido) return;
        $("#stepProductos").hide();
        $("#stepDatos").show();
    }

    function anteriorPaso() {
        $("#stepDatos").hide();
        $("#stepProductos").show();
    }

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

    function validarFormFacturar(){
        var correos = $("#txtCorreo").val().split(",");
        var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        for(var i = 0; i < correos.length; i++){
            if(!regexCorreo.test(correos[i].trim())){
                swalFocus("Error", "El correo electrónico '"+correos[i].trim()+"' no es válido", "error", "txtCorreo");
                return;
            }
        }
        var correosAdicionales = $("#txtCorreoAdicional").val().trim();
        if(correosAdicionales !== ""){
            var adicionales = correosAdicionales.split(",");
            for(var i = 0; i < adicionales.length; i++){
                if(!regexCorreo.test(adicionales[i].trim())){
                    swalFocus("Error", "El correo adicional '"+adicionales[i].trim()+"' no es válido", "error", "txtCorreoAdicional");
                    return;
                }
            }
        }
        validarFormulario('formFacturar');
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
<?php
}else{
?>
<script>
    $.fancybox.close();
    Swal.fire("Atención", "Debes asignar una tienda a la sucursal <?= $pedido["sucursal"] ?>", "warning");
</script>
<?php
}
?>
