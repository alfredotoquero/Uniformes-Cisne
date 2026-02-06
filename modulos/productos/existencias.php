<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");

$claseProductos = new Productos();
$_POST["idproducto"] = $_GET["idproducto"];

$claseSucursales = new Sucursales();
$claseAlmacenes = new Almacenes();

$_POST["almacenesusuario"] = $_SESSION["usuario"]["almacenes"];
$_POST["tiposucursal"] = 1;
$sucursales1 = $claseSucursales->obtenerSucursales($_POST);
$_POST["tiposucursal"] = 2;
$sucursales2 = $claseSucursales->obtenerSucursales($_POST);

$_POST["tipoalmacen"] = 1;
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);

$producto = $claseProductos->obtenerProducto($_POST)["producto"];

?>

<div style="width:1100px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Existencias</h4>
        </div>
    </div>
    <hr>

    <div class="box">

        <div class="box-body">
            <form name="formBusqueda2" id="formBusqueda2">
                <input type="hidden" name="archivo" id="archivo" value="/modulos/productos/desgloseexistencias.php">
                <input type="hidden" name="contenedor" id="contenedor" value="divExistenciasAlmacen">
                <input type="hidden" name="idproducto" id="idproducto" value="<?= $producto["idproducto"] ?>">
                <input type="hidden" name="idalmacen" id="idalmacen" value="0">

            </form>

            <div class="table-responsive">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th width="30">Producto</th>
                            <?
                            foreach ($sucursales1["sucursales"] as $sucursal) {
                            ?>
                                <th align="center"><?= $sucursal["nombre"]; ?></th>
                            <?
                            }
                            foreach ($sucursales2["sucursales"] as $sucursal) {
                            ?>
                                <th align="center"><?= $sucursal["nombre"]; ?></th>
                            <?
                            }
                            foreach ($almacenes["almacenes"] as $almacen) {
                            ?>
                                <th align="center"><?= $almacen["nombre"]; ?></th>
                            <?
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $producto["nombre"]; ?></td>
                            <?
                            // existencias por sucursal
                            // para sacar las existencias por sucursal, se debe conseguir el almacen y sumar las existencias donde el idproducto (ignorando color y talla) sea igual al recibido
                            foreach ($sucursales1["sucursales"] as $sucursal) {
                                $_POST["idalmacen"] = $sucursal["idalmacen"];
                                $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                                $almacenHabilitado = $claseProductos->tieneAlmacenAsignado($_POST);
                                file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba.txt", print_r($_POST,true));
                                $existencias = $datos["existencias"];
                                $reservado = $datos["reservado"];

                                if ($reservado > 0) {
                                    $total = $existencias . "/" . $reservado;
                                } else {
                                    $total = $existencias;
                                }

                            ?>
                                <td align="center">
                                    <?
                                    // si es un producto sin colores ni tallas y tiene el almacen deshabilitado, no debe tener enlace al desglose
                                    if ($claseProductos->manejaTallasColores($_POST) & $almacenHabilitado) {
                                    ?>
                                        <a href="javascript:;" onClick="seleccionarAlmacen('<?= $sucursal["idalmacen"] ?>','formBusqueda2');" style="margin-right:15px;"><?= $total; ?></a>
                                </td>
                            <?
                                    } else {
                                        if ($almacenHabilitado) {
                                            echo $total;
                                        } else {
                                            echo "<i class='fa fa-ban' style='color:red;'></i>";
                                        }
                                    }
                                }
                                foreach ($sucursales2["sucursales"] as $sucursal) {
                                    $_POST["idalmacen"] = $sucursal["idalmacen"];
                                    $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                                    $existencias = $datos["existencias"];
                                    $reservado = $datos["reservado"];

                                    if ($reservado > 0) {
                                        $total = $existencias . "/" . $reservado;
                                    } else {
                                        $total = $existencias;
                                    }
                            ?>
                            <td align="center">
                                <?
                                    // si es un producto sin colores ni tallas, no debe tener enlace al desglose
                                    if ($claseProductos->manejaTallasColores($_POST)) {
                                ?>
                                    <a href="javascript:;" onClick="seleccionarAlmacen('<?= $sucursal["idalmacen"] ?>','formBusqueda2');" style="margin-right:15px;"><?= $total; ?></a>
                            </td>
                        <?
                                    } else {
                                        echo $total;
                                    }
                                }
                                foreach ($almacenes["almacenes"] as $almacen) {
                                    $_POST["idalmacen"] = $almacen["idalmacen"];
                                    $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);

                                    $existencias = $datos["existencias"];
                                    $reservado = $datos["reservado"];

                                    if ($reservado > 0) {
                                        $total = $existencias . "/" . $reservado;
                                    } else {
                                        $total = $existencias;
                                    }
                        ?>
                        <td align="center">
                            <?
                                    // si es un producto sin colores ni tallas, no debe tener enlace al desglose
                                    if ($claseProductos->manejaTallasColores($_POST)) {
                            ?>
                                <a href="javascript:;" onClick="seleccionarAlmacen('<?= $almacen["idalmacen"] ?>','formBusqueda2');" style="margin-right:15px;"><?= $total; ?></a>
                        </td>
                <?
                                    } else {
                                        echo $total;
                                    }
                                }
                ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="divExistenciasAlmacen"></div>

</div>