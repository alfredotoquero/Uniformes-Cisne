<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$almacenes = $claseReportes->obtenerReorden($_POST);

// file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba.txt",print_r($almacenes,true));

if ($almacenes["respuesta"] == "OK") {
?>
    <div class="table-responsive">
        <?
        if (count($almacenes["almacenes"]) > 0) { ?>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="checkAll" checked onchange="toggleAllChecks()">
                <label class="form-check-label">
                    <span class="font-weight-bold"><strong>Todos los almacenes</strong></span></label>
            </div>
            <hr>
        <? }
        foreach ($almacenes["almacenes"] as $almacen) {
        ?>
            <div class="form-check form-switch">
                <input class="form-check-input checkAlmacen" type="checkbox" role="switch" id="check-<?= $almacen['idalmacen'] ?>" checked onchange="toggleAlmacen(<?= $almacen['idalmacen'] ?>)">
                <label class="form-check-label">
                    <span class="font-weight-bold"><strong><? echo $almacen["almacen"]; ?></strong></span></label>
            </div>
            <table class="table table-striped b-t">
                <thead>
                    <tr>
                        <th></th>
                        <th>Producto</th>
                        <th width="100">Existencia</th>
                        <th width="100">Stock Mínimo</th>
                        <th width="100">Sugerido</th>
                        <th width="100">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach ($almacen["productos"] as $producto) {
                    ?>
                        <tr>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input checkProducto almacen-<?= $almacen['idalmacen'] ?>" type="checkbox" role="switch" id="productos-<?= $almacen['idalmacen'] ?>-<?= $producto['idproducto'] ?>-<?= $producto['idtalla'] ?>-<?= $producto['idcolor'] ?>" name="productos[<?= $almacen['idalmacen'] ?>][<?= $producto['idproducto'] ?>][<?= $producto['idtalla'] ?>][<?= $producto['idcolor'] ?>]" checked value="<?= $producto["cantidad_solicitada"] ?>">
                                </div>
                            </td>
                            <td><?= $producto["producto"] . " | Talla: " . $producto["talla"] . " | Color: " . $producto["color"]; ?></td>
                            <td><?= $producto["existencia"]; ?></td>
                            <td><?= $producto["stock_minimo"]; ?></td>
                            <td><?= $producto["cantidad_solicitada"]; ?></td>
                            <td><input type="text" class="form-control" placeholder='0' onchange="$('#productos-<?= $almacen['idalmacen'] ?>-<?= $producto['idproducto'] ?>-<?= $producto['idtalla'] ?>-<?= $producto['idcolor'] ?>').val(this.value); console.log(this.value);" value="<?= $producto["cantidad_solicitada"] ?>" ></td>
                        </tr>
                    <?
                    }
                    ?>
                </tbody>
            </table>
        <?
        }
        ?>
    </div>
<?php
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $almacenes["mensaje"] ?>
        </div>
    </div>
<?
}
?>
<script>
    function toggleAllChecks() {
        var checks = $(".checkAlmacen");
        var checksProductos = $(".checkProducto");
        if ($("#checkAll").is(":checked")) {
            checks.prop("checked", true);
            checksProductos.prop("checked", true);
        } else {
            checks.prop("checked", false);
            checksProductos.prop("checked", false);
        }
    }

    function toggleAlmacen(idalmacen) {
        var checks = $(".almacen-" + idalmacen);
        if ($("#check-" + idalmacen).is(":checked")) {
            checks.prop("checked", true);
        } else {
            checks.prop("checked", false);
        }
    }
</script>