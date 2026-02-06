<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$_POST["idmovimientoinventario"] = $_GET["idmovimientoinventario"];

$claseMovimientos = new Movimientos();
$movimiento = $claseMovimientos->obtenerMovimiento($_POST)["movimiento"];

?>
<div style="width:800px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Detalle del movimiento</h4>
        </div>
    </div>
    <hr>
    <form name="formRecepcion" id="formRecepcion">
        <input type="hidden" name="controlador" id="controlador" value="movimientos">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/movimientos/ver/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="listaProductos">
        <input type="hidden" name="accion" id="accion" value="">
        <input type="hidden" name="idmovimientoinventario" id="idmovimientoinventario" value="<?= $movimiento["idmovimientoinventario"] ?>">
        <input type="hidden" name="idalmacen" id="idalmacen" value="<?= $movimiento["idalmacen"] ?>">
        <input type="hidden" name="autorizacion" id="autorizacion" value="">
        
        <input type="hidden" name="motivo" id="motivo" value="">

        <div id="listaProductos">
            
        </div>
    </form>
</div>

<script>
    $(document).ready(function (e){
        cargarDatosContenedor("formRecepcion");
    });
</script>