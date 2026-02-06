<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");

$sat = new SAT();
$regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Emisor</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="emisores">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtRazonSocial" class="form-label">Razón social<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRazonSocial" id="txtRazonSocial" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social">
        </div>
        <div class="mb-3">
            <label for="txtRFC" class="form-label">RFC<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRFC" id="txtRFC" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar el RFC">
        </div>
        <div class="mb-3">
            <label for="txtCodigoPostal" class="form-label">Código postal<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCodigoPostal" id="txtCodigoPostal" placeholder="Ingresa el código postal" autocomplete="off" data-mensajeerror="Debes indicar el código postal">
        </div>
        <div class="mb-3">
            <label for="slcRegimenFiscal" class="form-label">Régimen fiscal<span>*</span></label>
            <select class="form-control requerido" name="slcRegimenFiscal" id="slcRegimenFiscal" data-mensajeerror="Debes indicar el régimen fiscal">
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
        <hr>
        <div class="row">
            <div class="col-12 col-md-6">
                <label for="txtSerie" class="form-label">Serie<span>*</span></label>
                <input type="text" class="form-control requerido" name="txtSerie" id="txtSerie" placeholder="Ingresa la serie" autocomplete="off" data-mensajeerror="Debes indicar la serie">
            </div>
            <div class="col-12 col-md-6">
                <label for="txtFolio" class="form-label">Folio<span>*</span></label>
                <input type="text" class="form-control requerido" name="txtFolio" id="txtFolio" placeholder="Ingresa el folio" autocomplete="off" data-mensajeerror="Debes indicar el folio">
            </div>
        </div>
        <hr>
        <strong>Certificado de sello digital</strong>
        <div class="mb-3">
            <label for="flCertificado" class="form-label">Certificado<span>*</span></label>
            <input type="file" class="form-control" name="flCertificado" id="flCertificado">
        </div>
        <div class="mb-3">
            <label for="flLlave" class="form-label">Llave<span>*</span></label>
            <input type="file" class="form-control" name="flLlave" id="flLlave">
        </div>
        <div class="mb-3">
            <label for="txtPassword" class="form-label">Contraseña de la llave<span>*</span></label>
            <input type="password" class="form-control requerido" name="txtPassword" id="txtPassword" placeholder="Ingresa la contraseña de la llave" autocomplete="off" data-mensajeerror="Debes indicar la contraeña de la llave">
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>