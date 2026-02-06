<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

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

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Categoria</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="categorias">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
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
                        <input type="checkbox" name="chkTallas[]" id="chkTalla<?= $talla["idtalla"] ?>" value="<?= $talla["idtalla"] ?>">&nbsp;&nbsp;<?= $talla["nombre"] ?><br>
                        <?
                    }
                    ?>
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>