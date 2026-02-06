<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");

$claseProduccion = new Produccion();

$diseno = $claseProduccion->getDiseno($_GET["idsolicituddiseno"])["diseno"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Revisar diseño</h4>
        </div>
    </div>
    <hr>
    <form name="formDiseno" id="formDiseno">
        <input type="hidden" name="controlador" id="controlador" value="produccion">
        <input type="hidden" name="accion" value="aprobardiseno">
        <input type="hidden" name="idespecificacion" value="<? echo $_GET["idespecificacion"]; ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="row">
            <div class="form-group col-6 offset-3">
            <?
            if (file_exists("../../imagenes/disenos/".$diseno["idsolicituddiseno"]."/".$diseno["imagen1"]) and $diseno["imagen1"] != "") {
                ?>
                <a href="/imagenes/disenos/<? echo $diseno["idsolicituddiseno"]; ?>/<? echo $diseno["imagen1"]; ?>" data-fancybox="gallery"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/disenos/<? echo $diseno["idsolicituddiseno"]; ?>/<? echo $diseno["imagen1"]; ?>" alt="" width="100%"></a>
                <?
            }
            ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="form-group col-6 offset-3">
            <?
            if (file_exists("../../imagenes/disenos/".$diseno["idsolicituddiseno"]."/".$diseno["imagen2"]) and $diseno["imagen2"] != "") {
                ?>
                <a href="/imagenes/disenos/<? echo $diseno["idsolicituddiseno"]; ?>/<? echo $diseno["imagen2"]; ?>" data-fancybox="gallery"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/disenos/<? echo $diseno["idsolicituddiseno"]; ?>/<? echo $diseno["imagen2"] ?>" alt="" width="100%"></a>
                <?
            }
            ?>
            </div>
            
        </div>
        <div class="form-group mt-2">
            <textarea name="txtComentarios" id="txtComentarios" rows="8" class="form-control" readonly><? echo $diseno["comentarios"]; ?></textarea>
        </div>
        <div class="form-group mt-2 text-center">
            <a href="javascript:;" class="btn btn-danger waves-light waves-effect" onClick="fancy('/modulos/produccion/rechazardiseno.php?idsolicituddiseno=<? echo $_GET["idsolicituddiseno"]; ?>&idespecificacion=<? echo $_GET["idespecificacion"]; ?>');">Rechazar Diseño</a>
            <a href="javascript:;" class="btn btn-primary waves-light waves-effect" onClick="validarFormulario('formDiseno');">Aprobar Diseño</a>
        </div>
    </form>
</div>