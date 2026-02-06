<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/SAT.php");

$sat = new SAT();
$regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];

$_POST["idemisor"] = $_GET["idemisor"];

$claseEmisores = new Emisores();
$emisor = $claseEmisores->obtenerEmisor($_POST)["emisor"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Emisor</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="emisores">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idemisor" id="idemisor" value="<?= $_GET["idemisor"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtRazonSocial" class="form-label">Razón social<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRazonSocial" id="txtRazonSocial" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social" value="<?= $emisor["razon_social"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtRFC" class="form-label">RFC<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtRFC" id="txtRFC" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar el RFC" value="<?= $emisor["rfc"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtCodigoPostal" class="form-label">Código postal<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCodigoPostal" id="txtCodigoPostal" placeholder="Ingresa el código postal" autocomplete="off" data-mensajeerror="Debes indicar el código postal" value="<?= $emisor["codigo_postal"] ?>">
        </div>
        <div class="mb-3">
            <label for="slcRegimenFiscal" class="form-label">Régimen fiscal<span>*</span></label>
            <select class="form-control requerido" name="slcRegimenFiscal" id="slcRegimenFiscal" data-mensajeerror="Debes indicar el régimen fiscal">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($regimenesfiscales as $regimenfiscal) {
                    ?>
                    <option value="<?= $regimenfiscal["idregimenfiscal"]; ?>" <?= ($regimenfiscal["idregimenfiscal"]==$emisor["idregimenfiscal"]) ? "selected" : "" ?>><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 col-md-6">
                <label for="txtSerie" class="form-label">Serie<span>*</span></label>
                <input type="text" class="form-control requerido" name="txtSerie" id="txtSerie" placeholder="Ingresa la serie" autocomplete="off" data-mensajeerror="Debes indicar la serie" value="<?= $emisor["serie"] ?>">
            </div>
            <div class="col-12 col-md-6">
                <label for="txtFolio" class="form-label">Folio<span>*</span></label>
                <input type="text" class="form-control requerido" name="txtFolio" id="txtFolio" placeholder="Ingresa el folio" autocomplete="off" data-mensajeerror="Debes indicar el folio" value="<?= $emisor["folio"] ?>">
            </div>
        </div>
        <hr>
        <strong>Certificado de sello digital</strong> <small>(solo si se requiere editar)</small>
        <div class="mb-3">
            <label for="flCertificado" class="form-label">Certificado</label>
            <input type="file" class="form-control" name="flCertificado" id="flCertificado">
        </div>
        <div class="mb-3">
            <label for="flLlave" class="form-label">Llave</label>
            <input type="file" class="form-control" name="flLlave" id="flLlave">
        </div>
        <div class="mb-3">
            <label for="txtPassword" class="form-label">Contraseña de la llave</label>
            <input type="password" class="form-control" name="txtPassword" id="txtPassword" placeholder="Ingresa la contraseña de la llave" autocomplete="off" data-mensajeerror="Debes indicar la contraeña de la llave">
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>