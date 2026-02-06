<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseCatalogos = new Catalogos();
$catalogos = $claseCatalogos->obtenerCatalogos($_POST);

// $_POST["idproducto"] = $_GET["idproducto"];

$claseProductos = new Productos();
// $producto = $claseProductos->obtenerProductos($_POST)["producto"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Catálogos</h4>
        </div>
    </div>
    <hr>
    <form id="formCatalogos" name="formCatalogos">
        <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
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
                        ?>
                        <div class="row">
                            <div class="col-6">
                                <?= $valor["nombre"] ?>
                            </div>
                            <div class="col-6">
                                <input type="checkbox" name="<?= $catalogo["nombre"] . $catalogo["idcatalogo"] ?>[]" id="<?= $valor["nombre"] . $valor["idvalor"] ?>" data-switch="none" value="<?= $valor["idvalor"]; ?>">
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
                            ?>
                            <option value="<?= $valor["idvalor"]; ?>"><?= $valor["nombre"] ?></option>
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
</div>