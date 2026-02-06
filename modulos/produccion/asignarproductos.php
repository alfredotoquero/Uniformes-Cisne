<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");

$claseAlmacenes = new Almacenes();

$almacenes = $claseAlmacenes->obtenerAlmacenes(array())["almacenes"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Productos</h4><small>Indica la cantidad de producto que deseas asignar para cada una de las partidas.</small>
        </div>
    </div>
    <hr>
    <form name="formAsignar" id="formAsignar">
        <input type="hidden" name="controlador" id="controlador" value="produccion">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/produccion/asignarproductos/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divListaAsignar">
        <input type="hidden" name="accion" value="asignarproductos">
        <input type="hidden" name="idespecificacion" value="<?= $_GET["idespecificacion"]; ?>">
        <input type="hidden" name="idpedido" value="<?= $_GET["idpedido"]; ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">

        <div id="divListaAsignar"></div>
        
        <div class="row mt-2">
            <div class="col-12 col-sm-auto pt-1">
                <p>Almacén:</p>
            </div>
            <div class="col-12 col-sm-4">
                <select name="slcAlmacen" id="slcAlmacen" class="form-control requerido" onChange="cargarDatosContenedor('formAsignar')">
                    <?
                    foreach($almacenes as $almacen){
                    ?>
                    <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["nombre"] ?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-sm text-end">
                <button type="button" onClick="asignarProductosProduccion();" class="btn btn-primary btn-sm" id="btnAsignar">ASIGNAR PRODUCTOS</button>
            </div>
        </div>
    </form>
</div>
<script>
    $(document).ready(function(e){
        cargarDatosContenedor("formAsignar");
    });
</script>