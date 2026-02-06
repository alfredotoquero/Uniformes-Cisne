<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Compras.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");

$claseCompras = new Compras();
$claseAlmacenes = new Almacenes();

$compra = $claseCompras->obtenerCompra($_POST)["compra"];

$almacenes = $claseAlmacenes->obtenerAlmacenes(array())["almacenes"];

if (!isset($_POST["slcAlmacen"])) {
    $_POST["slcAlmacen"] = 1;
}

if (in_array($compra["status"],array("R","P")) && $claseCompras->alMenosUnoSinAsignar($_POST)) {
    $almenosunoasignar = true;
}

$requerido = 0;
$solicitado = 0;
$productos = $claseCompras->obtenerProductosCompra($_POST);
foreach ($productos["productos"] as $producto) {

    $requerido += (int)$producto["cantidad"];
    $solicitado += (int)$producto["cantidad_recibida"];
}

if ($compra["status"] != 'C') {
    if ($almenosunoasignar || ($solicitado / $requerido) * 100 < 100) {
        ?>
        <div class="row">
            <div class="col-12 col-sm-auto pt-1">
                <p>Almacén:</p>
            </div>
            <div class="col-12 col-sm-4">
                <select name="slcAlmacen" id="slcAlmacen" class="form-control requerido" onChange="cargarDatosContenedor('formRecibir');">
                    <?
                    foreach ($almacenes as $almacen) {
                    ?>
                        <option value="<?= $almacen["idalmacen"] ?>" <? if($almacen["idalmacen"]==$_POST["slcAlmacen"]){?>selected<?} ?>><?= $almacen["nombre"] ?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
        </div>
        <?
    }
}
?>
<table class="table table-striped b-t">
    <thead>
        <tr>
            <th width="50">Cant.</th>
            <? if ($compra["status"] != 'C') { ?><th width="120" class="text-center">Rec.</th><? } ?>
            <th>Producto</th>
            <th width="100">Talla</th>
            <th width="100">Color</th>
            <th width="100">Pedido</th>
            <th width="200">Usuario</th>
            <?
            // si esta compra está recibida total o parcialmente, habilitar esto
            if ($almenosunoasignar) {
                ?>
                    <th width="120">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" checked onchange="cambiarChecks(this,'chkProducto')">
                            <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                        </div>
                    </th>
                <?
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?
        $requerido = 0;
        $solicitado = 0;
        $productos = $claseCompras->obtenerProductosCompra($_POST);
        foreach ($productos["productos"] as $producto) {

            $requerido += (int)$producto["cantidad"];
            $solicitado += (int)$producto["cantidad_recibida"];
            $cantidad = $producto["cantidad"] - $producto["cantidad_recibida"];
        ?>
            <tr>
                <td><?= $producto["cantidad"]; ?></td>
                <? if ($compra["status"] != 'C') { ?><td class="text-center" >
                        <?
                        if ($producto["cantidad_recibida"] < $producto["cantidad"]) {
                        ?>
                            <input type="hidden" name="idcompraproductos[]" value="<?= $producto["idcompraproducto"]; ?>">
                            <?
                            if ($producto["cantidad_recibida"]>0) {
                                ?>
                                <div class="row">
                                    <div class="col-9">
                                        <input type="text" class="form-control txtCantidad" name="txtCantidades[]" data-maximo="<?= $cantidad; ?>" data-total="<?= $producto["cantidad"]; ?>">
                                    </div>
                                    <div class="col-3">
                                        <span>
                                            &nbsp;/<?= $producto["cantidad_recibida"] ?>
                                        </span>
                                    </div>
                                </div>
                                <?
                            }else {
                                ?>
                                <div class="row">
                                    <div class="col-9">
                                        <input type="text" class="form-control txtCantidad" name="txtCantidades[]" data-maximo="<?= $cantidad; ?>" data-total="<?= $producto["cantidad"]; ?>">
                                    </div>
                                </div>
                                <?
                            }
                            ?>
                        <?
                        } else {
                            echo $producto["cantidad_recibida"];
                        }
                        ?>
                    </td><? } ?>
                <td><?= (($producto["cantidad"]==$producto["cantidad_asignada"]) ? "<i class=\"fas fa-check-circle text-success\"></i>" : "") . $producto["producto"] . (($producto["notas"] != "") ? "<br><small>" . $producto["notas"] . "</small>" : ""); ?></td>
                <td><?= $producto["talla"]; ?></td>
                <td><?= $producto["color"]; ?></td>
                <td><?= $producto["idpedido"]; ?></td>
                <td><?= $producto["usuario"]; ?></td>
                <?
                if ($almenosunoasignar) {
                ?>
                    <td id="">
                        <?
                        $_POST["producto"] = $producto;
                        $_POST["cantidad"] = $producto["cantidad_recibida"] - $producto["cantidad_asignada"];
                        if (!$claseCompras->estaSurtido($producto) && $producto["cantidad_recibida"]>$producto["cantidad_asignada"] && $claseCompras->almacenConDisponibilidad($_POST) && $producto["idpedido"]>0) {
                            ?>
                            <input type="hidden" id="idcompraproducto" name="idcompraproductos2[]" value="<?= $producto["idcompraproducto"] ?>">
                            <input type="hidden" id="idpedido" name="idpedidos[]" value="<?= $producto["idpedido"] ?>">
                            <input type="hidden" id="idespecificacion" name="idespecificaciones[]" value="<?= $producto["idespecificacion"] ?>">
                            <input type="hidden" id="idproducto" name="idproductos[]" value="<?= $producto["idproducto"] ?>">
                            <input type="hidden" id="idtalla" name="idtallas[]" value="<?= $producto["idtalla"] ?>">
                            <input type="hidden" id="idcolor" name="idcolores[]" value="<?= $producto["idcolor"] ?>">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input chkProducto" role="switch" name="cantidades[]" checked value="<?= $producto["cantidad_recibida"] - $producto["cantidad_asignada"] ?>">
                                <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                            </div>

                            <?
                        }
                        ?>
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
<input type="hidden" name="totalrequerido" id="totalrequerido" value="<?= $requerido; ?>">
<input type="hidden" name="totalsolicitado" id="totalsolicitado" value="<?= $solicitado; ?>">
<div class="row">
    <div class="col-12 col-sm-6">
        <p>Total de productos: <?= $requerido; ?></p>
    </div>
    <div class="col-12 col-sm-6">
        <? if ($compra["status"] != 'C') { ?><p style="text-align: right">El porcentaje de producto asignado es <span id="spPorcentaje" style="font-weight: bold;"><?= number_format(($solicitado / $requerido) * 100, 2); ?>%</span></p><? } ?>
    </div>
</div>
<? if ($compra["status"] != 'C') {
    // !in_array($compra["status"],array('C','R'))
    if (($solicitado / $requerido) * 100 < 100) {
        ?>
        <div class="row mt-3">
            <div class="col-12 text-right">
                <button type="button" onClick="asignarProductos(2);" class="btn btn-success btn-sm">RECIBIR Y FINALIZAR</button>&nbsp;&nbsp;
                <button type="button" onClick="asignarProductos(1);" class="btn btn-primary btn-sm">RECIBIR PARCIALMENTE</button>&nbsp;&nbsp;
                <button type="button" onClick="asignarProductos(3);" class="btn btn-primary btn-sm">RECIBIR Y SOLICITAR</button>
                <button type="button" onClick="cancelarCompra();" class="btn btn-danger btn-sm">CANCELAR COMPRA</button>&nbsp;&nbsp;
            </div>
        </div>
        <?
    }
} ?>
<script>
    $(document).ready(function(e) {
        $(".txtCantidad").keyup(function() {
            calcularPorcentaje();
        });

        <?
        if ($almenosunoasignar) {
            ?>
            $("#divBtnAsignar").html('<button class="btn btn-primary waves-effect waves-light" type="button" onclick="cambiarAccionFormulario2(\'formRecibir\',\'asignarproductos\')">Asignar</button>');
            <?
        }else {
            ?>
            $("#divBtnAsignar").html("");
            <?
        }
        ?>
    });
</script>