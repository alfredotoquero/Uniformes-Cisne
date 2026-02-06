<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseProductos = new Productos();

if (isset($_GET["idproducto"])) {
    $_POST["idproducto"] = $_GET["idproducto"];
}
$producto = $claseProductos->obtenerProducto($_POST)["producto"];
$_POST["ordenar"] = 1;
$tallas = $claseProductos->obtenerTallasProducto($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<form id="formVariante" name="formVariante" class="mt-3">
    <input type="hidden" name="controlador" id="controlador" value="productos">
    <input type="hidden" name="accion" id="accion" value="guardarvariantes">
    <input type="hidden" name="idproducto" value="<? echo $producto["idproducto"]; ?>">
    <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
    
    
    <div class="box-body">
        <div class="table-responsive">
        <table class="table m-0">
            <thead>
                <tr>
                    <?
                    foreach ($tallas["tallas"] as $talla) {
                        ?>
                        <th width="30"><? echo $talla["talla"]; ?></th>
                        <?
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                <?
                foreach ($tallas["tallas"] as $talla){
                    $_POST["idtalla"] = $talla["idtalla"];
                    $variante = $claseProductos->obtenerVariantePrecio($_POST)["variante"]["variante"];
                    ?>
                    <td>
                        <input type="text" class="form-control" name="txtVariante<? echo $talla["idtalla"]; ?>" id="txtVariante<? echo $talla["idtalla"]; ?>" value="<? echo $variante; ?>">
                    </td>
                    <?
                }
                ?>
                </tr>
            </tbody>
        </table>
        
        </div>
    </div>
    
    <button type="button" onclick="validarFormulario('formVariante');" class="btn btn-primary">Guardar</button>
</form>