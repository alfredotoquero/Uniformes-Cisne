<?

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
$clasePedidos = new Pedidos();

$especificaciones = $clasePedidos->obtenerEspecificacionesPedidoSinValidacion($_POST); 

if ($especificaciones["respuesta"]=="OK") {
    ?>
    <h6><?($especificaciones["especificaciones"])?><h6>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Asignados</th>
                <th>Fecha Ult. Asig.</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($especificaciones["especificaciones"] as $especificacion){
                $color = ((isset($especificacion["color"])) ? $especificacion["color"] : "N/A");
                $talla = ((isset($especificacion["talla"])) ? $especificacion["talla"] : "N/A");
                ?>
                <tr id="trProducto<?= $especificacion["idproducto"]; ?>">
                    <td><?= $especificacion["producto"] ?></td>
                    <td><?= $color ?></td>
                    <td><?= $talla ?></td>
                    <td><?= $especificacion["cantidad"] ?></td>
                    <td><?= ($especificacion["cantidad_surtida"]==$especificacion["cantidad"]) ? "<span style=\"color:green;\">".$especificacion["cantidad_surtida"]."</span>": "<span style=\"color:red;\">".$especificacion["cantidad_surtida"]."</span>" ?></td>
                    <td><? echo( $especificacion["fecha_ultima_asignacion"] ? fecha_formateada($especificacion["fecha_ultima_asignacion"]) : "N/A") ?></td>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
</div>
    <?
} else {
    echo "<br><br><strong><center>" . $especificaciones["respuesta"] . "</strong></center>";
}
?>