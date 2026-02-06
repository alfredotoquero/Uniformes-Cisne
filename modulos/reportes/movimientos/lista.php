<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$movimientos = $claseReportes->obtenerMovimientos($_POST);

if($movimientos["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Producto</th>
                <th>Tipo de Movimiento</th>
                <th>Folio</th>
                <th>Acción</th>
                <th width="200">Almacen</th>
                <th width="80">Talla</th>
                <th width="80">Color</th>
                <th width="80">Cantidad</th>
                <th width="200">Existencias Almacen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($movimientos["movimientos"] as $movimiento){
                ?>
                <tr>
                    <td><?= fecha_formateada($movimiento["fecha"]); ?></td>
                    <td><?= $movimiento["usuario"]; ?></td>
                    <td><?= $movimiento["producto"]; ?></td>
                    <td><?= (($movimiento["origenmovimiento"]=="P") ? "Producción" : (($movimiento["origenmovimiento"]=="M") ? "Movimiento" : (($movimiento["origenmovimiento"]=="C") ? "Compra" : (($movimiento["origenmovimiento"]=="V") ? "Venta" : (($movimiento["origenmovimiento"]=="A") ? "Apartado" : (($movimiento["origenmovimiento"]=="D") ? "Devolucion" : "")))))); ?></td>
                    <td><?= $movimiento["folio"]; ?></td>
                    <td><?= (($movimiento["tipomovimiento"]=="E") ? "Entrada" : "Salida"); ?></td>
                    <td><?= $movimiento["almacen"]; ?></td>
                    <td><?= $movimiento["talla"]; ?></td>
                    <td><?= $movimiento["color"]; ?></td>
                    <td><?= ($movimiento["tipomovimiento"]=="E") ? "+".$movimiento["cantidad"] : "<span style=\"color:red;\">-".$movimiento["cantidad"]."</span>"; ?></td>
                    <td><?= $movimiento["existenciasalmacen"]; ?></td>
                </tr>
                <?
                $total += $producto["total"];
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7"></th>
                <th>$<?= number_format($total,2); ?></th>
            </tr>
        </tfoot>
    </table>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $movimientos["mensaje"] ?>
    </div>
</div>
<?
}
?>