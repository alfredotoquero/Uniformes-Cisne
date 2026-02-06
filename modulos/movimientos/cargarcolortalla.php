<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$claseProductos = new Productos();
$claseMovimientos = new Movimientos();

$_POST["idproducto"] = $_GET["idproducto"];
$_POST["ordenar"] = 1;
$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

$colores = $claseProductos->obtenerColoresProducto($_POST);
$tallas = $claseProductos->obtenerTallasProducto($_POST);
?>

<div style="width:1100px;height:90%;">
    <form name="formColorTalla" id="formColorTalla">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"]; ?>">
        <input type="hidden" name="idalmacen" id="idalmacen" value="<?= $_GET["idalmacen"]; ?>">
        <input type="hidden" name="accion" id="accion" value="<?= $_GET["accion"]; ?>" disabled>
        <input type="hidden" name="idpartida" id="idpartida" value="<?= $_GET["idpartida"]; ?>">
        <input type="hidden" name="tipomovimiento" id="tipomovimiento" value="<?= $_GET["tipomovimiento"]; ?>">
        <div class="table-responsive">
            <table class="table m-0">
                <thead class="sticky-header">
                    <tr>
                        <th width="30">Color\Talla</th>
                        <?
                        // tallas
                        foreach($tallas["tallas"] as $talla) {
                            ?>
                            <th><?= $talla["talla"]; ?></th>
                            <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?
                    // colores
                    foreach($colores["colores"] as $color){
                        $_POST["idcolor"] = $color["idcolor"];
                        ?>
                        <tr>
                            <td><?= $color["color"]; ?></td>
                            <?
                            foreach ($tallas["tallas"] as $talla) {
                                $_POST["idtalla"] = $talla["idtalla"];
                                $desglose = $claseMovimientos->obtenerPartidaTMP($_POST)["partida"];
                                ?>
                                <td>
                                    <input type="text" name="txtCantidad<?= $color["idcolor"] . "-" . $talla["idtalla"]; ?>" id="txtCantidad<?= $color["idcolor"] . "-" . $talla["idtalla"]; ?>" class="form-control cantidades" value="<?= $desglose["cantidad"]>0 ? $desglose["cantidad"] : ""; ?>" onkeypress="return isNumber(event);">
                                </td>
                                <? 
                            }
                            ?>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-5"></div>
            <div class="col-4">
                <button type="button" onClick="validarM();" class="btn btn-primary btn-sm">SELECCIONAR</button>
            </div>
        </div>
    </form>
</div>