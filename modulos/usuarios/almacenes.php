<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");

$_POST["idusuario"] = $_GET["idusuario"];

$claseAlmacenes = new Almacenes();
$claseUsuarios = new Usuarios();

$usuario = $claseUsuarios->obtenerUsuario($_POST)["usuario"];
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST)["almacenes"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Almacenes</h4>
        </div>
    </div>
    <hr>
    <form id="formAlmacen" name="formAlmacen">
        <input type="hidden" name="controlador" id="controlador" value="usuarios">
        <input type="hidden" name="accion" id="accion" value="asignaralmacenes">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $_GET["idusuario"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <div class="row">
                <?
                    foreach ($almacenes as $almacen) {
                        $arrayalmacenes = explode(",",$usuario["almacenes"]);
                        ?>
                        <div class="col-6">
                            <?= $almacen["nombre"]; ?>
                        </div>
                        <div class="col-6">
                            <!-- <input type="checkbox" name="chkAlmacenes[]" id="chkAlmacen<?= $almacen["idalmacen"] ?>" data-switch="none" value="<?= $almacen["idalmacen"]; ?>" <? if(strpos($usuario["almacenes"],$almacen["idalmacen"])!==false){?> checked <?} ?>> -->
                            <input type="checkbox" name="chkAlmacenes[]" id="chkAlmacen<?= $almacen["idalmacen"] ?>" data-switch="none" value="<?= $almacen["idalmacen"]; ?>" <? if(in_array($almacen["idalmacen"],$arrayalmacenes)){?> checked <?} ?>>
                            <label for="chkAlmacen<?= $almacen["idalmacen"] ?>" data-on-label="" data-off-label=""></label>
                        </div>
                        <?
                    }
                    ?>
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formAlmacen');" class="btn btn-primary">Guardar</button>
    </form>
</div>