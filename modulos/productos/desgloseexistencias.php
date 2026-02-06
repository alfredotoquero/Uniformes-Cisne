<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");

$claseProductos = new Productos();
$claseAlmacenes = new Almacenes();

$almacen = $claseAlmacenes->obtenerAlmacen($_POST)["almacen"];

$_POST["ordenar"] = 1;
$tallas = $claseProductos->obtenerTallasProducto($_POST);
$colores = $claseProductos->obtenerColoresProducto($_POST);
?>
<div class="box">
    <div class="box-header">
        <div class="p-2">
            <div class="row">
                <div class="col-sm-12 text-center font-weight-bold">
                    <h3><?= $almacen["nombre"] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-sm btn-primary mb-2" onclick="actualizarStockMinimo()" id="btnActualizarStockMinimo">Actualizar Stock Mínimo</button>
                <button type="button" class="btn btn-sm btn-success mb-2" style="display:none" id="btnGuardar" onclick="validarFormulario('formStockMinimo')">Guardar</button>
                <button type="button" class="btn btn-sm btn-danger mb-2" style="display:none" id="btnCancelar" onclick="cancelar()">Cancelar</button>
            </div>
        </div>
        <form name="formStockMinimo" id="formStockMinimo">
            <input type="hidden" name="controlador" id="controlador" value="productos">
            <input type="hidden" name="accion" id="accion" value="actualizarstockminimo">
            <input type="hidden" name="idproducto" id="idproducto" value="<?= $_POST["idproducto"] ?>">
            <input type="hidden" name="idalmacen" id="idalmacen" value="<?= $_POST["idalmacen"] ?>">
            <input type="hidden" name="ordenar" id="ordenar" value="1">
            <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
            <div class="table-responsive2">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th width="30">Color\Talla</th>
                            <?
                            // tallas
                            foreach ($tallas["tallas"] as $talla) {
                            ?>
                                <th width="30" class="text-center"><?= $talla["talla"]; ?></th>
                            <?
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        // colores
    
                        foreach ($colores["colores"] as $color) {
                            ?>
                            <tr>
                                <td><?= $color["color"]; ?></td>
                                <?
                                foreach ($tallas["tallas"] as $talla) {
                                    $_POST["idtalla"] = $talla["idtalla"];
                                    $_POST["idcolor"] = $color["idcolor"];
                                    $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                                    $existencias = $datos["existencias"];
                                    $reservado = $datos["reservado"];
                                    $stock_minimo = $datos["stock_minimo"];
                                    $stock_ideal = $datos["stock_ideal"];
    
                                    if ($reservado > 0) {
                                        $total = $existencias . "/" . $reservado;
                                    } else {
                                        $total = $existencias;
                                    }
    
                                ?>
                                    <td class="text-center">
                                        <?= $total; ?><br>
                                        <input type="text" name="txtStockMinimo[]" class="form-control mt-1 text-center mask2Numbers" style="max-width: 100px; display: none;" value="<?= (($stock_minimo>0) ? $stock_minimo . (($stock_ideal>0) ? "/" . $stock_ideal : "") : (($stock_ideal>0) ? "0/".$stock_ideal : "")) ?>">
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
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.mask2Numbers').mask('999/999', {
            maxLength: 4,
            minLength: 3,
            placeholder: " "
        });
    });

    function actualizarStockMinimo() {
        $('input[name="txtStockMinimo[]"]').show();
        $('.diagonal').show();
        $('#btnActualizarStockMinimo').toggle();
        $('#btnGuardar').toggle();
        $('#btnCancelar').toggle();
    }

    function cancelar() {
        $('input[name="txtStockMinimo[]"]').hide();
        $('input[name="txtStockMinimo[]"]').val("");
        $('.diagonal').hide();
        $('#btnActualizarStockMinimo').toggle();
        $('#btnGuardar').toggle();
        $('#btnCancelar').toggle();
    }

</script>