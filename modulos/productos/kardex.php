<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseProductos = new Productos();
$_POST["idproducto"] = $_GET["idproducto"];

$almacenes = $claseProductos->obtenerAlmacenesProducto($_POST);
$colores = $claseProductos->obtenerColoresProducto($_POST);
$tallas = $claseProductos->obtenerTallasProducto($_POST);

$producto = $claseProductos->obtenerProducto($_POST)["producto"];

?>

<div style="width:1100px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Kardex</h4>
        </div>
    </div>
    <hr>

    <div class="padding">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col">
                        <h4><?= $producto["nombre"] ?></h4>
                    </div>
                </div>
            </div>
    
            <div class="box-body">
                <form name="formBusqueda2" id="formBusqueda2">
                    <input type="hidden" name="archivo" id="archivo" value="/modulos/productos/kardex/lista.php">
                    <input type="hidden" name="contenedor" id="contenedor" value="divListaKardex">
                    <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
                    <div class="row">
                        <div class="col-xs-12 col-md-3">
                            <input type="text" class="form-control filtro" name="txtFolio" id="txtFolio" value="<? echo $_POST["txtFolio"]; ?>" placeholder="Folio" onKeyUp="cargarDatosContenedor('formBusqueda2');">
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <select name="slcTalla" id="slcTalla" class="form-control" onChange="cargarDatosContenedor('formBusqueda2');">
                                <option value="0">TODAS LAS TALLAS</option>
                                <?
                                foreach ($tallas["tallas"] as $talla) {
                                    ?>
                                    <option value="<?= $talla["idtalla"] ?>"><?= $talla["talla"] ?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <select name="slcColor" id="slcColor" class="form-control" onChange="cargarDatosContenedor('formBusqueda2');">
                                <option value="0">TODOS LOS COLORES</option>
                                <?
                                foreach ($colores["colores"] as $color) {
                                    ?>
                                    <option value="<?= $color["idcolor"] ?>"><?= $color["color"] ?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <select name="slcAlmacen" id="slcAlmacen" class="form-control" onChange="cargarDatosContenedor('formBusqueda2');">
                                <option value="0">TODOS LOS ALMACENES</option>
                                <?
                                foreach ($almacenes["almacenes"] as $almacen) {
                                    ?>
                                    <option value="<?= $almacen["idalmacen"] ?>"><?= $almacen["almacen"] ?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
                <div id="divListaKardex"><? include($_SERVER["DOCUMENT_ROOT"]."/modulos/productos/kardex/lista.php"); ?></div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function (e) {
        $("#formBusqueda2 :input[type=text]").on("keypress", function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        $("#formBusqueda2 select").on("change", function (e) {
            e.preventDefault();
        });

    });
</script>