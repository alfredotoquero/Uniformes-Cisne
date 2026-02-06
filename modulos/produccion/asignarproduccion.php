<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");

$claseProduccion = new Produccion();

$productos = $claseProduccion->getProductosProduccionEspecificacion($_GET["idespecificacion"]);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Iniciar Producción</h4><small>Indica las partidas que iniciarán el proceso de producción.</small>
        </div>
    </div>
    <hr>
    <form name="formProduccion" id="formProduccion">
        <input type="hidden" name="controlador" id="controlador" value="produccion">
        <input type="hidden" name="accion" id="accion" value="asignarproduccion">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/produccion/listaproduccion.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divListaProduccion">
        <input type="hidden" name="idespecificacion" value="<?= $_GET["idespecificacion"]; ?>">
        <input type="hidden" name="idcliente" value="<?= $_GET["idcliente"]; ?>">
        <input type="hidden" name="idpedido" value="<?= $_GET["idpedido"]; ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <div id="divListaProduccion">
            
        </div>
        <div class="row mt-2">
            <div class="col-12 text-end">
                <button type="button" onClick="finalizarProduccion();" class="btn btn-primary btn-sm" id="btnAsignar">FINALIZAR PRODUCCIÓN</button>
                <? if($productos["produccion"]==1){ ?>
                    <button type="button" onClick="iniciarProduccion();" class="btn btn-primary btn-sm" id="btnAsignar">INICIAR PRODUCCIÓN</button>
                <? } ?>
            </div>
        </div>
    </form>
</div>
<script>
    $(document).ready(function (e) {
        cargarDatosContenedor("formProduccion");
    });

    function iniciarProduccion(){
        if($(".chkDesglose:checked").length>0){
            // $("#formProduccion").submit();
            validarFormulario("formProduccion");
        }else{
            alert("ATENCION: Debes indicar al menos una partida para iniciar la producción.");
        }
    }

    function finalizarProduccion(){
        $("#accion").val("finalizarproduccion");
        validarFormulario("formProduccion");
    }
</script>