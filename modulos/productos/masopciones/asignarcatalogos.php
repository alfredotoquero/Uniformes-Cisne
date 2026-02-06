<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseCatalogos = new Catalogos();
$catalogos = $claseCatalogos->obtenerCatalogos($_POST);

if (isset($_GET["idproducto"])) {
    $_POST["idproducto"] = $_GET["idproducto"];
}

$claseProductos = new Productos();

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<form id="formCatalogos" name="formCatalogos" class="mt-3">
    <input type="hidden" name="controlador" id="controlador" value="productos">
    <input type="hidden" name="idproducto" id="idproducto" value="<?= $_POST["idproducto"] ?>">
    <input type="hidden" name="accion" id="accion" value="asignarcatalogos">
    <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
    <?
    foreach ($catalogos["catalogos"] as $catalogo) {
        $_POST["idcatalogo"] = $catalogo["idcatalogo"];
        $valores = $claseCatalogos->obtenerValores($_POST);
        ?>
        <div class="mb-3">
            <label for="txtNombre" class="form-label"><?= $catalogo["nombre"] ?></label>
            <?
            if ($catalogo["multiple"]) {
                foreach ($valores["valores"] as $valor) {
                    $_POST["idvalor"] = $valor["idvalor"];
                    ?>
                    <div class="row">
                        <div class="col-6">
                            <?= $valor["nombre"] ?>
                        </div>
                        <div class="col-6">
                            <input type="checkbox" name="<?= $catalogo["nombre"] . $catalogo["idcatalogo"] ?>[]" id="<?= $valor["nombre"] . $valor["idvalor"] ?>" data-switch="none" value="<?= $valor["idvalor"]; ?>" <? if($claseProductos->tieneCatalogoAsignado($_POST)){?> checked <?} ?>>
                            <label for="<?= $valor["nombre"] . $valor["idvalor"] ?>" data-on-label="" data-off-label=""></label>
                        </div>
                    </div>
                    <?
                }
            } else {
                ?>
                <select name="<?= $catalogo["nombre"] . $catalogo["idcatalogo"] ?>" id="<?= $catalogo["nombre"] . $catalogo["idcatalogo"] ?>" class="form-control">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($valores["valores"] as $valor) {
                        $_POST["idvalor"] = $valor["idvalor"];
                        ?>
                        <option value="<?= $valor["idvalor"]; ?>" <? if($claseProductos->tieneCatalogoAsignado($_POST)){?> selected <?} ?> ><?= $valor["nombre"] ?></option>
                        <?
                    }
                    ?>
                </select>
                <?
            }
            ?>
        </div>
        <?
    }
    ?>
    <button type="button" onclick="validarFormulario('formCatalogos');" class="btn btn-primary">Guardar</button>
</form>