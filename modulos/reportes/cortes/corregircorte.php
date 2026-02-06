<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseCatalogos = new Catalogos();
$claseReportes = new Reportes();

$formaspago = $claseCatalogos->obtenerCatFormasPago();

$_POST["idcorte"] = $_GET["idcorte"];
$corte = $claseReportes->obtenerCorte($_POST)["corte"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Corregir Arqueos</h4>
        </div>
    </div>
    <hr>
    <form id="formCorte" name="formCorte">
        <input type="hidden" name="controlador" id="controlador" value="reportes">
        <input type="hidden" name="accion" id="accion" value="corregirarqueocorte">
        <input type="hidden" name="idcorte" id="idcorte" value="<?= $_GET["idcorte"] ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <?
        $_POST["idcorte"] = $_GET["idcorte"];
        foreach ($formaspago["formaspago"] as $formapago) {
            $_POST["idformapago"] = $formapago["idformapago"];
            $total = $claseReportes->obtenerMontoArqueoCorte($_POST)["monto"];
            ?>
            <div class="mb-3">
                <label for="txtFormapagoArqueoFancy<?= $formapago["idformapago"] ?>" style="float:left;"><?= $formapago["nombre"] ?> </label>
                <input type="text" id="txtFormapagoArqueoFancy<?= $formapago["idformapago"] ?>" name="txtFormapagoArqueoFancy<?= $formapago["idformapago"] ?>" class="form-control mt-3" placeholder="<?= $formapago["nombre"]?>" value="<?= (($total>0) ? $total : "") ?>">
            </div>
            <?
        }
        ?>
        <div class="mb-3">
            <label for="txtFeria" style="float:left;">Feria</label>
            <input type="text" id="txtFeria" name="txtFeria" class="form-control mt-3" placeholder="Feria" value="<?= (($corte["feria"]>0) ? $corte["feria"] : "") ?>">
        </div>
        <button type="button" onclick="validarFormulario('formCorte');" class="btn btn-primary">Guardar</button>
    </form>
</div>