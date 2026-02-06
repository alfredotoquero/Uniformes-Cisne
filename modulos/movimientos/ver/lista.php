<?
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Movimientos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Almacenes.php");

$claseMovimientos = new Movimientos();
// $movimiento = $claseMovimientos->obtenerMovimiento($_POST);
$movimiento = $claseMovimientos->obtenerMovimiento2($_POST);


$claseAlmacenes = new Almacenes();
$misalmacenes = $_SESSION["usuario"]["almacenes"];

if ($movimiento["respuesta"] == "OK" && !empty($movimiento["movimiento"]["productos"])) {
    $movimiento = $movimiento["movimiento"];
    $detalle = "";
    $detalle .= "Tipo de movimiento: <b>" . ($movimiento["idtipomovimiento"] == "1" ? "Entrada" : ($movimiento["idtipomovimiento"] == "2" ? "Salida" : "Traspaso")) . "</b>";
    $_POST["idalmacen"] = $movimiento["idalmacen"];
    $almacen = $claseAlmacenes->obtenerAlmacen($_POST)["almacen"];
    $_POST["idalmacen"] = $movimiento["idalmacensecundario"];
    $almacens = $claseAlmacenes->obtenerAlmacen($_POST)["almacen"];
    $detalle .= "<br># de movimiento: <b>" . $movimiento["idmovimientoinventario"] . "</b>";
    $detalle .= "<br>Solicitado por: <b>" . $movimiento["usuario"] . "</b>";
    $detalle .= ($movimiento["notas"] != "") ? "<br>Notas: " . $movimiento["notas"] : "";
    $detalle .= "<br>Almacen" . ($almacens["nombre"] != "" ? " origen" : "") . ": <b>" . $almacen["nombre"] . "</b>";
    if ($movimiento["usuariosalida"] != "") {
        $detalle .= "<br>Autorizado por: <b>" . $movimiento["usuariosalida"] . "</b>";
    }
    $detalle .= $movimiento["idalmacensecundario"] > 0 ? "<br>Almacen destino: <b>" . $almacens["nombre"] . "</b>" : "";
    if ($movimiento["usuarioentrada"] != "") {
        $detalle .= "<br>" . (($movimiento["status"] == "C") ? "Recepción cancelada" : "Recibido") . " por: <b>" . $movimiento["usuarioentrada"] . "</b>";
        $detalle .= "<br>" . (($movimiento["status"] == "C") ? "Motivo de cancelación: " . $movimiento["motivo"] : "");
    }
    $detalle .= "<br>Fecha: <b>" . fecha_formateada_largo($movimiento["fecha"]) . "</b>";
    if ($movimiento["idtipomovimiento"] == 3) {
        $detalle .= ("<br>Estado: <b>" . ($movimiento["autorizacion"] == 0 ? "Sin autorizar" : ($movimiento["autorizacion"] == 1 ? "Cancelado" : ($movimiento["recepcionparcial"] == 0 ? "Autorizado" : "Recibido"))) . "</b>");
    }
    $porrecibir = ((strpos($misalmacenes, $movimiento["idalmacensecundario"]) !== false && $movimiento["idtipomovimiento"] == 3 && $movimiento["recepcionparcial"] == 0 && $movimiento["autorizacion"] == 2) ? true : false);

    $recibido = (($movimiento["idtipomovimiento"] == 3 && in_array($movimiento["recepcionparcial"], array("1", "2"))) ? true : false);


    unset($_SESSION["authToken"]);
    $_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>

    <input type="hidden" name="authToken" id="authToken" value="<?= $_SESSION["authToken"] ?>">

    <div class="row">
        <div class="col-sm-12">
            <?= $detalle; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12" style="text-align: right;">
            <?
            if ($porrecibir) {
            ?>
                <a href="javascript:;" onClick="recibirProductos();" class="btn btn-primary waves-effect waves-light">Recibir Productos</a>&nbsp;&nbsp;
                <a href="javascript:;" onClick="cancelarRecepcion();" class="btn btn-danger waves-effect waves-light">Cancelar Recepción</a>&nbsp;&nbsp;
            <?
            }

            // si el almacen origen es el almacen del usuario y aún no ha sido autorizado, debe aparecer la opcion para que lo pueda aceptar o rechazar
            if ((strpos($misalmacenes, $movimiento["idalmacen"]) !== false) && $movimiento["autorizacion"] == 0) {
            ?>
                <a href="javascript:;" onClick="realizarAutorizacion('autorizar');" class="btn btn-primary waves-effect waves-light">Autorizar</a>&nbsp;&nbsp;
                <a href="javascript:;" onClick="realizarAutorizacion('noautorizar');" class="btn btn-danger waves-effect waves-light">No Autorizar</a>
            <?
            }
            ?>
        </div>
    </div>
    <table class="table table-striped b-t">
        <thead>
            <tr>
                <?
                if ($porrecibir) {
                ?>
                    <th width="75"></th>
                <?
                }
                ?>
                <th width="50">Cant</th>
                <?
                if ($recibido) {
                ?>
                    <th width="120">Cantidad Recibida</th>
                <?
                }
                ?>
                <th>Producto</th>
                <th width="100">Talla</th>
                <th width="100">Color</th>
            </tr>
        </thead>
        <tbody>
            <?
            // foreach($movimiento["productos"] as $producto) {
            $productos = explode("::", $movimiento["productos"]);
            foreach ($productos as $infoproducto) {
                $desgloses = explode("-_-", $infoproducto);
                foreach ($desgloses as $desglose) {
                    $p = explode(";", $desglose);
                    $producto["idmovimientoinventarioproducto"] = $p[0];
                    $producto["producto"] = $p[1];
                    $producto["talla"] = $p[2];
                    $producto["color"] = $p[3];
                    $producto["cantidad"] = $p[4];
                    $producto["cantidadrecibida"] = $p[5];
            ?>
                    <tr>
                        <?
                        if ($porrecibir) {
                        ?>
                            <td>
                                <input type="hidden" name="idmovimientoproducto[]" id="" value="<?= $producto["idmovimientoinventarioproducto"]; ?>">
                                <input type="text" name="txtCantidad[]" id="txtCantidad-<?= $producto["idmovimientoinventarioproducto"]; ?>" class="form-control" onKeypress="isNumber(event);">
                            </td>
                        <?
                        }
                        ?>
                        <td><?= $producto["cantidad"]; ?></td>
                        <?
                        if ($recibido) {
                        ?>
                            <td><?= $producto["cantidadrecibida"]; ?></td>
                        <?
                        }
                        ?>
                        <input type="hidden" name="cantidadProducto[]" id="cantidadProducto-<?= $producto["idmovimientoinventarioproducto"] ?>" value="<?= $producto["cantidad"]; ?>">
                        <td><?= $producto["producto"]; ?></td>
                        <td><?= $producto["talla"]; ?></td>
                        <td><?= $producto["color"]; ?></td>
                    </tr>
            <?
                }
            }
            ?>
        </tbody>
    </table>
<?
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <? //= $movimiento["mensaje"] 
            ?>
            <?= "No se encontraron partidas en el movimiento."; ?>
        </div>
    </div>
<?
}
?>