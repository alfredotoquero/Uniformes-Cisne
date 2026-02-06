<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();
?>
<div class="box-body">
    <div class="mt-3 mb-3" style="text-align: right;">
        <button type="button" onclick="toggleDiv('formRazones','btnAgregarR')" class="btn btn-primary" id="btnAgregarR">Agregar</button>
    </div>
    <form name="formRazones" id="formRazones" style="display:none;">
        <input type="hidden" name="controlador" id="controlador" value="clientes">
        <input type="hidden" name="accion" id="accion" value="agregarrazon">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/razones/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divRazones">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
        <input type="hidden" name="idrazonsocial" id="idrazonsocial" value="">
        <div class="mt-3 mb-3">
            <label for="txtRazonSocial" class="form-label">Razón Social<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRazonSocial" id="txtRazonSocial" placeholder="Ingresa la Razón Social" autocomplete="off" data-mensajeerror="Debes indicar una Razón Social">
        </div>
        <div class="mb-3">
            <label for="txtRFC" class="form-label">RFC<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRFC" id="txtRFC" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar un RFC">
        </div>
        <div class="mb-3">
            <label for="slcUsoCFDI" class="form-label">Uso CFDI<span>*</span></label>
            <select name="slcUsoCFDI" id="slcUsoCFDI" class="form-control">
                <option value="0">--Seleccionar--</option>
                <?
                $usoscfdi = $claseClientes->obtenerUsosCFDI();
                foreach ($usoscfdi["usoscfdi"] as $usocfdi) {
                    ?>
                    <option value="<?= $usocfdi["usocfdi"] ?>"><?= $usocfdi["usocfdi"] . " - " . $usocfdi["descripcion"] ?></option>
                    <?
                }
                ?>
            </select>
            <!-- <input type="text" class="form-control requerido" name="slcUsoCFDI" id="slcUsoCFDI" placeholder="Ingresa el" autocomplete="off" data-mensajeerror="Debes indicar "> -->
        </div>
        <div class="mb-3">
            <label for="slcRegimenFiscal" class="form-label">Régimen Fiscal<span>*</span></label>
            <select name="slcRegimenFiscal" id="slcRegimenFiscal" class="form-control">
                <option value="0">--Seleccionar--</option>
                <?
                $regimenesfiscales = $claseClientes->obtenerRegimenesFiscales();
                foreach ($regimenesfiscales["regimenesfiscales"] as $regimenfiscal) {
                    ?>
                    <option value="<?= $regimenfiscal["regimenfiscal"] ?>"><?= $regimenfiscal["regimenfiscal"] . " - " . $regimenfiscal["descripcion"] ?></option>
                    <?
                }
                ?>
            </select>
            <!-- <input type="text" class="form-control requerido" name="slcRegimenFiscal" id="slcRegimenFiscal" placeholder="Ingresa el" autocomplete="off" data-mensajeerror="Debes indicar "> -->
        </div>
        <div class="mb-3">
            <label for="txtCodigoPostal" class="form-label">Código Postal<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCodigoPostal" id="txtCodigoPostal" placeholder="Ingresa el Código Postal" autocomplete="off" data-mensajeerror="Debes indicar un Código Postal">
        </div>
        <button type="button" onclick="validarFormulario('formRazones');" class="btn btn-primary">Guardar</button>
        <button type="button" onclick="cancelarFormulario('formRazones','btnAgregarR')" class="btn btn-danger">Cancelar</button>
        <hr>
    </form>

    <div id="divRazones"></div>
</div>

<script>
    $(document).ready(function (e) {
        cargarDatosContenedor("formRazones");
    });
</script>