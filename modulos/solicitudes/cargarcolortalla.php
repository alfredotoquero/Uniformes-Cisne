<?
// origen
// idcategoria
// idproducto
// agregarpartida
// idpartida
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Tallas.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Solicitudes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");

$claseTallas = new Tallas();
$claseSolicitudes = new Solicitudes();
$claseProductos = new Productos();

$_POST = $_GET;
$idusuario = $_SESSION["usuario"]["idusuario"];
$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["idcategoria"] = $_POST["slcCategoria"];

$tallas = $claseTallas->obtenerTallasCategoria($_POST);

$partida = $claseSolicitudes->obtenerPartidaTMP($_POST["idpartida"])["partida"];

$_POST["idsolicitudproducto"] = $partida["idsolicitudproducto"];
?>
<script>
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function agregarRenglonColor(numero) {
        // se debe agregar un nuevo renglon para el siguiente posible color a agregar
        if ($("#banderaColor" + numero).val() == 0) {
            $('#tbTallasColores tbody').append('<tr>\
                <td>\
                    <input type="hidden" name="banderaColor' + (numero + 1) + '" id="banderaColor' + (numero + 1) + '" value="0">\
                    <input type="text" name="txtColor' + (numero + 1) + '" id="txtColor' + (numero + 1) + '" class="form-control" onkeydown="agregarRenglonColor(' + (numero + 1) + ');">\
                </td>\
                <?
                foreach ($tallas["tallas"] as $talla) {
                ?> <
                td > \
                <
                input type = "text"
                name = "txtCantidad' + (numero+1) + '<?= "-" . $talla["idtalla"]; ?>"
                id = "txtCantidad' + (numero+1) + '<?= "-" . $talla["idtalla"]; ?>"
                class = "form-control cantidades"
                value = ""
                onkeypress = "return isNumber(event);" > \
                <
                /td>\
            <?
                }
            ?> <
            /tr>');
            }
            $("#banderaColor" + numero).val(1);
        }
</script>

<div style="width:1100px;height:90%;">
    <form name="formColorTalla" id="formColorTalla">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"]; ?>">
        <input type="hidden" name="accion" id="accion" value="<?= $_GET["accion"]; ?>" disabled>
        <input type="hidden" name="idpartida" id="idpartida" value="<?= (($_GET["idpartida"] > 0) ? $_GET["idpartida"] : ""); ?>">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $idusuario; ?>">
        <input type="hidden" name="idsolicitudproducto" id="idsolicitudproducto" value="<?= $_GET["idsolicitudproducto"]; ?>">
        <input type="hidden" name="slcCategoria" id="slcCategoria" value="<?= $_GET["slcCategoria"]; ?>">
        <input type="hidden" name="txtProducto" id="txtProducto" value="<?= $_GET["txtProducto"]; ?>">
        <?
        // si se recibe idproducto, se muestra la tabla del producto
        // si se recibe idcategoriaproducto, se muestra la tabla de la categoria
        if (isset($_POST["idproducto"]) && $_POST["idproducto"] != "") {
            $producto = $claseProductos->obtenerProducto($_POST)["producto"];
            $_POST["idproducto"] = $producto["idproducto"];
            $_POST["ordenar"] = 1;
            $tallas = $claseProductos->obtenerTallasProducto($_POST);
            if ($tallas["respuesta"] == "OK") {
        ?>
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead class="sticky-header">
                            <tr>
                                <th width="30">Color\Talla</th>
                                <?
                                foreach ($tallas["tallas"] as $talla) {
                                ?>
                                    <th width="30"><?= $talla["talla"]; ?></th>
                                <?
                                }

                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            // colores
                            $colores = $claseProductos->obtenerColoresProducto($_POST);
                            foreach ($colores["colores"] as $color) {
                            ?>
                                <tr>
                                    <td><?= $color["color"]; ?></td>
                                    <?
                                    foreach ($tallas["tallas"] as $talla) {
                                        $_POST["idproducto"] = $producto["idproducto"];
                                        $_POST["idtalla"] = $talla["idtalla"];
                                        $_POST["idcolor"] = $color["idcolor"];
                                        $cantidad = $claseSolicitudes->obtenerCantidadTMP($_POST)["cantidad"];
                                    ?>
                                        <td>
                                            <input type="text" name="txtCantidad<?= $color["idcolor"] . "-" . $talla["idtalla"]; ?>" id="txtCantidad<?= $color["idcolor"] . "-" . $talla["idtalla"]; ?>" class="form-control cantidades" value="<?= (($cantidad != "") ? $cantidad : ""); ?>" onkeypress="return isNumber(event);">
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
                </div>
            <?
            } else {
                // si el producto no maneja tallas, sólo se agrega un input para la cantidad
                $solicitudproducto = $claseSolicitudes->obtenerPartida($_POST)["partida"];
            ?>
                <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="col-2">Cantidad:</div>
                    <div class="col-10">
                        <input type="text" id="txtCantidad" name="txtCantidad" class="form-control" placeholder="Cantidad" value="<?= $solicitudproducto["cantidad"]; ?>">
                    </div>
                </div>
            <?
            }
        } else if (isset($_POST["idcategoria"])) {
            if ($tallas["respuesta"] == "OK") {
            ?>
                <div class="table-responsive">
                    <table class="table m-0" id="tbTallasColores">
                        <thead class="sticky-header">
                            <tr>
                                <th width="30">Color\Talla</th>
                                <?
                                foreach ($tallas["tallas"] as $talla) {
                                ?>
                                    <th width="30"><?= $talla["nombre"]; ?></th>
                                <?
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            // se cargan primero todos los colores agregados
                            // se carga el input donde tentativamente se escribiria el siguiente color
                            $colores = $claseSolicitudes->obtenerColoresPartidaTMP($_POST);
                            $num = 1;
                            foreach ($colores["colores"] as $color) {
                            ?>
                                <tr>
                                    <td><input type="text" name="txtColor<?= $num; ?>" id="txtColor<?= $num; ?>" class="form-control" value="<?= $color["color"]; ?>"></td>
                                    <?
                                    foreach ($tallas["tallas"] as $talla) {
                                        $_POST["idsolicitudproducto"] = $partida["idsolicitudproducto"];
                                        $_POST["idtalla"] = $talla["idtalla"];
                                        $_POST["color"] = $color["color"];
                                        $cantidad = $claseSolicitudes->obtenerCantidadTMP($_POST)["cantidad"];
                                    ?>
                                        <td>
                                            <input type="text" name="txtCantidad<?= $num . "-" . $talla["idtalla"]; ?>" id="txtCantidad<?= $num . "-" . $talla["idtalla"]; ?>" class="form-control cantidades" value="<?= (($cantidad != "") ? $cantidad : ""); ?>" onkeypress="return isNumber(event);">
                                        </td>
                                    <?
                                    }
                                    ?>
                                </tr>
                            <?
                                $num++;
                            }
                            ?>
                            <tr>
                                <td><input type="hidden" name="banderaColor<?= $num; ?>" id="banderaColor<?= $num; ?>" value="0"><input type="text" name="txtColor<?= $num; ?>" id="txtColor<?= $num; ?>" class="form-control" onkeydown="agregarRenglonColor(<?= $num; ?>);"></td>
                                <?
                                foreach ($tallas["tallas"] as $talla) {
                                ?>
                                    <td>
                                        <input type="text" name="txtCantidad<?= $num; ?><?= "-" . $talla["idtalla"]; ?>" id="txtCantidad<?= $num; ?><?= "-" . $talla["idtalla"]; ?>" class="form-control cantidades" onkeypress="return isNumber(event);">
                                    </td>
                                <?
                                }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?
            } else {
                // si el producto no maneja tallas, sólo se agrega un input para la cantidad
                $solicitudproducto = $claseSolicitudes->obtenerPartidaTMP($_POST)["partida"];
            ?>
                <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="col-2">Cantidad:</div>
                    <div class="col-10">
                        <input type="text" id="txtCantidad" name="txtCantidad" class="form-control" placeholder="Cantidad" value="<?= $solicitudproducto["cantidad"]; ?>">
                    </div>
                </div>
        <?
            }
        }
        ?>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-5"></div>
            <div class="col-4">
                <button type="button" onClick="validarS();" class="btn btn-primary btn-sm">SELECCIONAR</button>
            </div>
        </div>

    </form>
</div>