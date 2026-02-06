<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");

$_POST["portipotalla"] = true;

$claseTallas = new Tallas();
$tallas1 = $claseTallas->obtenerTallas($_POST);

// $_POST["tipotalla"] = 2;

// $claseTallas = new Tallas();
// $tallas2 = $claseTallas->obtenerTallas($_POST);

// $_POST["tipotalla"] = 3;

// $claseTallas = new Tallas();
// $tallas3 = $claseTallas->obtenerTallas($_POST);

$_POST["idcategoriaproducto"] = $_GET["idcategoriaproducto"];

$claseCategorias = new Categorias();
$categoria = $claseCategorias->obtenerCategoria($_POST)["categoria"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Categoria</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="categorias">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idcategoriaproducto" id="idcategoriaproducto" value="<?= $_GET["idcategoriaproducto"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $categoria["nombre"] ?>">
        </div>
        <div class="row">
            <div class="mb-3">
                <label for="chkTallas" class="form-label">Tallas</label><br>
            </div>
            <div class="col-4">
                <?
                $div2 = true;
                $div3 = true;
                foreach ($tallas1["tallas"] as $talla) {
                    $_POST["idtalla"] = $talla["idtalla"];
                    if ($talla["tipo"]==2 && $div2) {
                        ?>
                        </div><div class="col-4">
                        <?
                        $div2 = false;
                    }
                    if ($talla["tipo"]==3 && $div3) {
                        ?>
                        </div><div class="col-4">
                        <?
                        $div3 = false;
                    }
                    ?>
                    <input type="checkbox" name="chkTallas[]" id="chkTalla<?= $talla["idtalla"] ?>" value="<?= $talla["idtalla"] ?>" <? if($claseCategorias->tieneTalla($_POST)["respuesta"]=="OK"){?> checked <?} ?>>&nbsp;&nbsp;<?= $talla["nombre"] ?><br>
                    <?
                }
                ?>
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>