<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$ventas = $claseReportes->obtenerVentasProductos($_POST);

if($ventas["respuesta"]=="OK"){
?>
<div class="table-responsive" id="divReporte">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Sucursal</th>
                <th>Vendedor</th>
                <th>Fecha</th>
                <th>Ticket</th>
                <th>Producto</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($ventas["productos"] as $producto){
                ?>
                <tr>
                    <td><?= $producto["sucursal"]; ?></td>
                    <td><?= $producto["vendedor"]; ?></td>
                    <td><?= fecha_formateada($producto["fecha"]); ?></td>
                    <td><?= $producto["ticket"]; ?></td>
                    <td><?= $producto["producto"]; ?></td>
                    <td><?= $producto["color"]; ?></td>
                    <td><?= $producto["talla"]; ?></td>
                    <td><?= $producto["cantidad"]; ?></td>
                    <td>$<?= number_format($producto["total"],2); ?></td>

                </tr>
                <?
                $total += $producto["total"];
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8"></th>
                <th>$<? echo number_format($total,2); ?></th>
            </tr>
        </tfoot>
    </table>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $ventas["mensaje"] ?>
    </div>
</div>
<?
}
?>