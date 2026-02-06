<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$sucursales = $claseReportes->obtenerInventario($_POST);

if($sucursales["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <?
    foreach ($sucursales["sucursales"] as $sucursal) {
        if ($sucursal["productos"]!="") {
        ?>
        <span class="font-weight-bold"><strong><? echo $sucursal["nombre"]; ?></strong></span>
        <table class="table table-striped b-t">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                    <th>Tipo de Movimiento</th>
                    <th width="100">Almacen</th>
                    <th width="100">Cantidad</th>
                    <th width="100">Existencias</th>
                    <th width="200">Existencias Almacen</th>
                </tr>
            </thead>
            <tbody>
            <?
            $fechas = explode(",",$sucursal["fechas"]);
            $productos = explode(",",$sucursal["productos"]);
            $origenmovimientos = explode(",",$sucursal["origenmovimientos"]);
            $tipomovimientos = explode(",",$sucursal["tipomovimientos"]);
            $almacenes = explode(",",$sucursal["almacenes"]);
            $cantidades = explode(",",$sucursal["cantidades"]);
            $existencias = explode(",",$sucursal["existencias"]);
            $existenciasalmacenes = explode(",",$sucursal["existenciasalmacenes"]);

            foreach ($productos as $i => $producto) {
                ?>
                <tr>
                    <td><?= $producto; ?></td>
                    <td><?= fecha_formateada($fechas[$i]); ?></td>
                    <td><?= (($origenmovimientos[$i]=="P") ? "Producción" : (($origenmovimientos[$i]=="M") ? "Movimiento" : (($origenmovimientos[$i]=="C") ? "Compra" : (($origenmovimientos[$i]=="V") ? "Venta" : (($origenmovimientos[$i]=="A") ? "Apartado" : (($origenmovimientos[$i]=="D") ? "Devolucion" : "")))))); ?></td>
                    <td><?= (($tipomovimientos[$i]=="E") ? "Entrada" : "Salida"); ?></td>
                    <td><?= $almacenes[$i]; ?></td>
                    <td><?= $cantidades[$i]; ?></td>
                    <td><?= $existencias[$i]; ?></td>
                    <td><?= $existenciasalmacenes[$i]; ?></td>
                </tr>
                <?
            }
            ?>
            </tbody>
        </table>
        <?
        }
    }
    ?>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $sucursales["mensaje"] ?>
    </div>
</div>
<?
}
?>