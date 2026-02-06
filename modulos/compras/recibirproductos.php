<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Compras.php");

$claseCompras = new Compras();

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

?>

<script>
    $(document).ready(function(e){
        $(".txtCantidad").keyup(function() {
            calcularPorcentaje();
        });
    });
</script>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Recibir Productos <br><small>Indica la cantidad de producto que deseas recibir para cada una de las partidas.</small></h4>
        </div>
    </div>
    <hr>
    <form id="formRecibir" name="formRecibir">
        <input type="hidden" name="controlador" id="controlador" value="compras">
        <input type="hidden" name="accion" value="recibirproducto">
        <input type="hidden" name="idcompra" value="<? echo $_GET["idcompra"]; ?>">
        <input type="hidden" name="tipo" id="tipo" value="">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <table class="table table-striped b-t">
            <thead>
                <tr>
                    <th width="50">Cant.</th>
                    <th width="80">Solic.</th>
                    <th>Producto</th>
                    <th width="100">Talla</th>
                    <th width="100">Color</th>
                    <th width="100">Pedido</th>
                    <th width="200">Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?
                $requerido = 0;
                $solicitado = 0;
                $_POST["idcompra"] = $_GET["idcompra"];
                $productos = $claseCompras->obtenerProductosCompra($_POST);
                foreach ($productos["productos"] as $producto) {
                    $detalle = (($producto["producto"]!="") ? $producto["producto"] : "") . (($producto["talla"]!="") ? " | Talla: ".$producto["talla"] . (($producto["color"]!="") ? ", Color: ".$producto["color"] : "") : (($producto["color"]!="") ? " Color: ".$producto["color"] : ""));
                    
                    $requerido += (int)$producto["cantidad"];
                    $solicitado += (int)$producto["cantidad_recibida"];
                    $cantidad = $producto["cantidad"]-$producto["cantidad_recibida"];
                    ?>
                    <tr>
                        <td><?= $producto["cantidad"]; ?></td>
                        <td>
                        <?php
                        if($producto["cantidad_recibida"]<$producto["cantidad"]){
                        ?>
                        <input type="hidden" name="idcompraproductos[]" value="<? echo $producto["idcompraproducto"]; ?>">
                        <input type="text" class="form-control txtCantidad" name="txtCantidades[]" data-maximo="<? echo $cantidad; ?>" data-total="<? echo $producto["cantidad"]; ?>" >
                        <?php
                        }else{
                            echo $producto["cantidad_recibida"];
                        }
                        ?>
                        </td>
                        <td><?= $producto["producto"].(($producto["notas"]!="") ? "<br><small>".$producto["notas"]."</small>" : ""); ?></td>
                        <td><?= $producto["talla"]; ?></td>
                        <td><?= $producto["color"]; ?></td>
                        <td><?= $producto["idpedido"]; ?></td>
                        <td><?= $producto["usuario"]; ?></td>
                    </tr>
                        <?
                }
                ?>
            </tbody>
        </table>
        <input type="hidden" name="totalrequerido" id="totalrequerido" value="<? echo $requerido; ?>">
        <input type="hidden" name="totalsolicitado" id="totalsolicitado" value="<? echo $solicitado; ?>">
        </form>
        <div class="row">
            <div class="col-12 col-sm-6">
                <p>Total de productos: <? echo $requerido; ?></p>
            </div>
            <div class="col-12 col-sm-6">
                <p style="text-align: right">El porcentaje de producto asignado es <span id="spPorcentaje" style="font-weight: bold;"><? echo number_format(($solicitado/$requerido)*100,2); ?>%</span></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-right">
                <button type="button" onClick="asignarProductos(2);" class="btn btn-success btn-sm">RECIBIR Y FINALIZAR</button>&nbsp;&nbsp;
                <button type="button" onClick="asignarProductos(1);" class="btn btn-primary btn-sm">RECIBIR PARCIALMENTE</button>&nbsp;&nbsp;
                <button type="button" onClick="asignarProductos(3);" class="btn btn-primary btn-sm">RECIBIR Y SOLICITAR</button>
                <button type="button" onClick="asignarProductos(0);" class="btn btn-danger btn-sm">CANCELAR</button>&nbsp;&nbsp;
            </div>
        </div>
    </form>
</div>