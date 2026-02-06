<?

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

// $_POST = $_GET;
// print_r($_POST);
$claseProductos = new Productos();
$movimientos = $claseProductos->obtenerKardex($_POST);



if ($movimientos["respuesta"]=="OK") {
    ?>
    <div class="table-responsive">
        <table class="table table-striped b-t">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
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
                <?
                foreach ($movimientos["movimientos"] as $movimiento) {
                    ?>
                    <tr>
                        <td><?= fecha_formateada($movimiento["fecha"]); ?></td>
                        <td><?= $movimiento["usuario"]; ?></td>
                        <td><?= (($movimiento["origenmovimiento"]=="P") ? "Producción" : (($movimiento["origenmovimiento"]=="M") ? "Movimiento" : (($movimiento["origenmovimiento"]=="C") ? "Compra" : (($movimiento["origenmovimiento"]=="V") ? "Venta" : (($movimiento["origenmovimiento"]=="A") ? "Apartado" : (($movimiento["origenmovimiento"]=="D") ? "Devolucion" : "")))))); ?></td>
                        <td><?= (($movimiento["folio"]!="") ? $movimiento["folio"] : "-"); ?></td>
                        <td><?= (($movimiento["tipomovimiento"]=="E") ? "Entrada" : "Salida"); ?></td>
                        <td><?= $movimiento["almacen"]; ?></td>
                        <td><?= $movimiento["talla"]; ?></td>
                        <td><?= $movimiento["color"]; ?></td>
                        <td><?= ($movimiento["tipomovimiento"]=="E") ? "+".$movimiento["cantidad"] : "<span style=\"color:red;\">-".$movimiento["cantidad"]."</span>"; ?></td>
                        <td><?= $movimiento["existenciasalmacen"]; ?></td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
    </div>
    <?
} else {
    echo "<br><br><strong><center>" . $movimientos["mensaje"] . "</strong></center>";
}

?>