<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");

$_POST["idusuario"] = $_GET["idusuario"];

$claseUsuarios = new Usuarios();

$usuario = $claseUsuarios->obtenerUsuario($_POST)["usuario"];

$secciones = $claseUsuarios->obtenerSecciones($_POST)["secciones"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Permisos</h4>
        </div>
    </div>
    <hr>
    <form id="formPermiso" name="formPermiso">
        <input type="hidden" name="controlador" id="controlador" value="usuarios">
        <input type="hidden" name="accion" id="accion" value="asignarpermisos">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $_GET["idusuario"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <?
            $i = 0;
            foreach ($secciones as $seccion) {
                if ($i % 3 == 0) {
            ?>
                    <div class="row">
                    <?
                }

                $_POST["idusuario"] = $_GET["idusuario"];
                $_POST["idseccion"] = $seccion["idseccion"];

                $respuesta = $claseUsuarios->tienePermiso($_POST);
                    ?>
                    <div class="col-4">
                        <div class="row">
                            <div class="col-6">
                                <?= $seccion["nombre"]; ?>
                            </div>
                            <div class="col-3">
                                <input type="checkbox" name="chkPermisos[]" id="chkPermiso<?= $seccion["idseccion"] ?>" data-switch="none" value="<?= $seccion["idseccion"]; ?>" <? if ($respuesta["respuesta"] == "OK") { ?> checked <? } ?>>
                                <label for="chkPermiso<?= $seccion["idseccion"] ?>" data-on-label="" data-off-label="" style="font-size: 2em;"></label>
                            </div>
                            <div class="col-3">
                                <input type="checkbox" name="chkLecturas[]" id="chkLectura<?= $seccion["idseccion"] ?>" data-switch="none" value="1" <? if ($respuesta["solo_lectura"]) { ?> checked <? } ?>>&nbsp;&nbsp;Solo lectura
                                <label for="chkLectura<?= $seccion["idseccion"] ?>" data-on-label="" data-off-label=""></label>
                            </div>
                        </div>

                        <? if ($seccion["produccion"] == 1) { ?>
                            <div id="divProduccion" style="margin-top:10px; margin-bottom:10px; padding-left: 10px;">
                                <div class="row">
                                    <div class="col-6">
                                        Almacén
                                    </div>
                                    <div class="col-6">
                                        <input type="checkbox" name="chkAlmacen" id="chkAlmacen" data-switch="none" value="A" <? if (strpos($usuario["tipousuario"], "A") !== false) { ?>checked<? } ?>>
                                        <label for="chkAlmacen" data-on-label="" data-off-label=""></label><br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        Diseño
                                    </div>
                                    <div class="col-6">
                                        <input type="checkbox" name="chkDiseno" id="chkDiseno" data-switch="none" value="D" <? if (strpos($usuario["tipousuario"], "D") !== false) { ?>checked<? } ?>>
                                        <label for="chkDiseno" data-on-label="" data-off-label=""></label><br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        Serigrafía
                                    </div>
                                    <div class="col-6">
                                        <input type="checkbox" name="chkSerigrafia" id="chkSerigrafia" data-switch="none" value="S" <? if (strpos($usuario["tipousuario"], "S") !== false) { ?>checked<? } ?>>
                                        <label for="chkSerigrafia" data-on-label="" data-off-label=""></label><br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        Bordado
                                    </div>
                                    <div class="col-6">
                                        <input type="checkbox" name="chkBordado" id="chkBordado" data-switch="none" value="B" <? if (strpos($usuario["tipousuario"], "B") !== false) { ?>checked<? } ?>>
                                        <label for="chkBordado" data-on-label="" data-off-label=""></label><br>
                                    </div>
                                </div>




                            </div>
                        <? } ?>

                    </div>
                    <?
                    if ($i % 3 == 2) {
                    ?>
                    </div>
            <?
                    }
                    $i++;
                }
            ?>
            <div class="row">

            </div>
        </div>
        <button type="button" onclick="validarFormulario('formPermiso');" class="btn btn-primary">Guardar</button>
    </form>
</div>