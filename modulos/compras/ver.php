<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

?>

<script>
    $(document).ready(function(e){
        

        cargarDatosContenedor("formRecibir");
    });
</script>
<div style="width:1000px;">
    <div class="row">
        <div class="col-6">
            <h4 class="header-title">Compra # <?= $_GET["idcompra"] ?></h4>
        </div>
        <div class="col-6 text-end" id="divBtnAsignar"></div>
    </div>
    <hr>
    <form id="formRecibir" name="formRecibir">
        <input type="hidden" name="controlador" id="controlador" value="compras">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/compras/ver/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divListaRecibir">
        <input type="hidden" name="accion" id="accion" value="recibirproducto">
        <input type="hidden" name="idcompra" value="<?= $_GET["idcompra"]; ?>">
        <input type="hidden" name="tipo" id="tipo" value="">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        
        <div id="divListaRecibir"></div>
    </form>

</div>