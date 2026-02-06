<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Produccion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");

$claseProduccion = new Produccion();
$claseProductos = new Productos();

$_POST["idalmacen"] = $_POST["slcAlmacen"];
$productos = $claseProduccion->getProductosPendientesEspecificacion($_POST["idespecificacion"],$_POST["idalmacen"]);
?>
<table class="table table-striped b-t">
    <thead>
        <tr>
            <th width="80">CANT</th>
            <th width="50">REST</th>
            <th width="50">DISP</th>
            <th>PRODUCTO</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($productos["productos"] as $producto) {
            // recuperar el disponible para cada producto (tproductoexistencias?)
            if ($producto["idproducto"]>0) {
                $_POST["idproducto"] = $producto["idproducto"];
                $_POST["idtalla"] = $producto["idtalla"];
                $_POST["idcolor"] = $producto["idcolor"];
                $respuesta = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
            }else {
                 $respuesta = array("existencias"=>0,"reservado"=>0);

                //YA FUNCIONA: Falta restar las existencias del producto libre
                $_POST["idproducto"] = 0;
                $_POST["nombre"] = $producto["nombre"];
                $_POST["idtalla"] = $producto["idtalla"];
                $_POST["idcolor"] = $producto["idcolor"];
                $_POST["idespecificacionproducto"] = $producto["idespecificacionproducto"];
                $res = $claseProduccion->getExistenciasLibre($_POST);
                // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba.txt", print_r($res,true));
                $respuesta = array("existencias"=>$res["existencias"]["cantidad_recibida"],"reservado"=>$res["existencias"]["cantidad_surtida"]);
            }
        ?>
            <tr>
                <td>
                    <input type="hidden" name="idespecificacionproducto[]" value="<?= $producto["idespecificacionproducto"]; ?>">
                    <input type="text" class="form-control txtCantidad" name="txtCantidad[]" data-maximo="<?= $producto["cantidad"]; ?>" data-total="<?= $producto["total"]; ?>" <? if ($respuesta["existencias"] - $respuesta["reservado"] == 0) { ?>readonly<? } ?>>
                </td>
                <td><?= $producto["cantidad"]; ?></td>
                <td><?= ($respuesta["existencias"] - $respuesta["reservado"]); ?></td>
                <td><?= $producto["nombre"] . " | Talla: " . $producto["talla"] . ", Color: " . $producto["color"]; ?></td>
            </tr>
        <?
        }
        ?>
    </tbody>
</table>
<input type="hidden" name="totalrequerido" id="totalrequerido" value="<?= $productos["requerido"]; ?>">
<input type="hidden" name="totalsolicitado" id="totalsolicitado" value="<?= $productos["solicitado"]; ?>">
<div class="row">
    <div class="col-12 text-right">
        El porcentaje de producto asignado es <span id="spPorcentaje" style="font-weight: bold;"><?= (($productos["solicitado"] == 0 and $productos["requerido"] == 0) ? "0.00" : number_format(($productos["solicitado"] / $productos["requerido"]) * 100, 2)); ?>%</span>.
    </div>
</div>

<script>
    $(document).ready(function(e) {
        $(".txtCantidad").keyup(function() {
            calcularPorcentaje();
        });
    });
</script>