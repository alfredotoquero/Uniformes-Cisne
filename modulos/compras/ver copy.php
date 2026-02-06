<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Compras.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");

$_POST["idcompra"] = $_GET["idcompra"];

$claseCompras = new Compras();
$compra = $claseCompras->obtenerCompra($_POST)["compra"];

$_POST["idproveedor"] = $_GET["idproveedor"];

$claseProveedores = new Proveedores();
$proveedor = $claseProveedores->obtenerProveedor($_POST)["proveedor"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:800px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Productos de la compra: <?= $compra["idcompra"]; ?></h4>
        </div>
    </div>
    <hr>
    <div id="listaProductos">
        <div class="row">
            <div class="col-sm-12">
                <?= fecha_formateada_largo($compra["fecha"]); ?>
            </div>
        </div>
        <?
        $productos = $claseCompras->obtenerProductosCompra($_POST);
        if ($productos["respuesta"]=="OK") {
        ?>
            <table class="table table-striped b-t">
                <thead>
                    <tr>
                        <th width="50">Cant</th>
                        <th>Producto</th>
                        <th width="100">Talla</th>
                        <th width="100">Color</th>
                        <th width="100">Pedido</th>
                        <th width="200">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                <?
                foreach ($productos["productos"] as $producto) {
                    ?>
                    <tr>
                        <td><?= $producto["cantidad"]; ?></td>
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
        <?
        }
        ?>
    </div>
</div>