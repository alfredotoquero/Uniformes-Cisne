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
<div style="width:650px;">
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
        <div class="mb-3 permisos-lista">
            <?
            foreach ($secciones as $seccion) {
                $_POST["idusuario"] = $_GET["idusuario"];
                $_POST["idseccion"] = $seccion["idseccion"];

                $respuesta = $claseUsuarios->tienePermiso($_POST);
                $tieneAcceso = ($respuesta["respuesta"] == "OK");
                $soloLectura = $respuesta["solo_lectura"];

                $respPermisos = $claseUsuarios->obtenerPermisosSeccion($_POST);
                $listaPermisos = ($respPermisos["respuesta"] == "OK") ? $respPermisos["permisos"] : [];
            ?>
                <div class="permiso-row">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox"
                                name="chkPermisos[]"
                                id="chkPermiso<?= $seccion["idseccion"] ?>"
                                data-switch="none"
                                value="<?= $seccion["idseccion"]; ?>"
                                <?= $tieneAcceso ? "checked" : "" ?>
                                onchange="toggleSeccion(this, <?= $seccion["idseccion"] ?>, <?= $seccion["produccion"] == 1 ? "true" : "false" ?>)">
                            <label for="chkPermiso<?= $seccion["idseccion"] ?>" data-on-label="" data-off-label="" style="margin-bottom:0;"></label>
                            <span><?= $seccion["nombre"]; ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <input type="checkbox"
                                name="chkLecturas[<?= $seccion["idseccion"] ?>]"
                                id="chkLectura<?= $seccion["idseccion"] ?>"
                                data-switch="none"
                                value="1"
                                <?= $soloLectura ? "checked" : "" ?>
                                <?= !$tieneAcceso ? "disabled" : "" ?>>
                            <label for="chkLectura<?= $seccion["idseccion"] ?>" data-on-label="" data-off-label="" style="margin-bottom:0;"></label>
                            <small class="text-muted">Solo lectura</small>
                        </div>
                    </div>

                    <? if (!empty($listaPermisos)) { ?>
                        <div id="permisosSeccion<?= $seccion["idseccion"] ?>" class="permisos-especificos-wrap" style="<?= !$tieneAcceso ? "display:none;" : "" ?>">
                            <? foreach ($listaPermisos as $perm) { ?>
                                <div class="d-flex align-items-start gap-2 permiso-especifico-row">
                                    <input type="checkbox"
                                        name="chkPermisosEspecificos[]"
                                        id="chkPermEsp<?= $perm["idpermiso"] ?>"
                                        data-switch="none"
                                        value="<?= $perm["idpermiso"] ?>"
                                        <?= $perm["tiene_permiso"] ? "checked" : "" ?>
                                        <?= !$tieneAcceso ? "disabled" : "" ?>>
                                    <label for="chkPermEsp<?= $perm["idpermiso"] ?>" data-on-label="" data-off-label="" style="margin-bottom:0; flex-shrink:0;"></label>
                                    <div>
                                        <span class="fw-medium"><?= htmlspecialchars($perm["permiso"]) ?></span>
                                        <? if (!empty($perm["descripcion"])) { ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($perm["descripcion"]) ?></small>
                                        <? } ?>
                                    </div>
                                </div>
                            <? } ?>
                        </div>
                    <? } ?>

                    <? if ($seccion["produccion"] == 1) { ?>
                        <div id="divProduccion<?= $seccion["idseccion"] ?>" class="produccion-subrow" style="<?= !$tieneAcceso ? "display:none;" : "" ?>">
                            <div class="mb-1"><small class="text-muted fw-semibold">Opciones</small></div>
                            <div class="row text-center">
                                <div class="col-3">
                                    <input type="checkbox" name="chkAlmacen" id="chkAlmacen" data-switch="none" value="A" <? if (strpos($usuario["tipousuario"], "A") !== false) { ?>checked<? } ?>>
                                    <label for="chkAlmacen" data-on-label="" data-off-label=""></label>
                                    <div><small>Almacén</small></div>
                                </div>
                                <div class="col-3">
                                    <input type="checkbox" name="chkDiseno" id="chkDiseno" data-switch="none" value="D" <? if (strpos($usuario["tipousuario"], "D") !== false) { ?>checked<? } ?>>
                                    <label for="chkDiseno" data-on-label="" data-off-label=""></label>
                                    <div><small>Diseño</small></div>
                                </div>
                                <div class="col-3">
                                    <input type="checkbox" name="chkSerigrafia" id="chkSerigrafia" data-switch="none" value="S" <? if (strpos($usuario["tipousuario"], "S") !== false) { ?>checked<? } ?>>
                                    <label for="chkSerigrafia" data-on-label="" data-off-label=""></label>
                                    <div><small>Serigrafía</small></div>
                                </div>
                                <div class="col-3">
                                    <input type="checkbox" name="chkBordado" id="chkBordado" data-switch="none" value="B" <? if (strpos($usuario["tipousuario"], "B") !== false) { ?>checked<? } ?>>
                                    <label for="chkBordado" data-on-label="" data-off-label=""></label>
                                    <div><small>Bordado</small></div>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                </div>

            <? } ?>
        </div>
        <button type="button" onclick="validarFormulario('formPermiso');" class="btn btn-primary">Guardar</button>
    </form>
</div>

<style>
.permisos-lista {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}
.permiso-row {
    background: #f9f9f9;
    border-bottom: 1px solid #e9ecef;
    padding: 8px 14px;
    transition: background 0.15s ease;
}
.permiso-row:last-child {
    border-bottom: none;
}
.permiso-row:hover {
    background: #eef2ff;
}
.produccion-subrow {
    margin-top: 8px;
    padding: 8px 4px 4px;
    background: #e9ecef;
    border-radius: 4px;
    text-align: center;
}
.permisos-especificos-wrap {
    margin-top: 6px;
    padding-left: 28px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.permiso-especifico-row {
    padding: 4px 6px;
    border-radius: 4px;
}
.permiso-especifico-row:hover {
    background: #dde3f7;
}
/* Switches reducidos */
.permisos-lista input[data-switch] + label {
    width: 38px;
    height: 18px;
}
.permisos-lista input[data-switch] + label:before {
    font-size: 0.6rem;
    line-height: 18px;
}
.permisos-lista input[data-switch] + label:after {
    height: 12px;
    width: 12px;
    top: 3px;
    left: 3px;
}
.permisos-lista input[data-switch]:checked + label:after {
    left: 22px;
}
</style>

<script>
function toggleSeccion(chk, idseccion, esProduccion) {
    var chkLectura = document.getElementById('chkLectura' + idseccion);

    if (chk.checked) {
        chkLectura.disabled = false;
    } else {
        chkLectura.disabled = true;
        chkLectura.checked = false;
    }

    if (esProduccion) {
        var divProd = document.getElementById('divProduccion' + idseccion);
        if (divProd) {
            divProd.style.display = chk.checked ? '' : 'none';
        }
    }

    var divPermisos = document.getElementById('permisosSeccion' + idseccion);
    if (divPermisos) {
        divPermisos.style.display = chk.checked ? '' : 'none';
        var chksPermisos = divPermisos.querySelectorAll('input[type="checkbox"]');
        chksPermisos.forEach(function(c) {
            c.disabled = !chk.checked;
            if (!chk.checked) c.checked = false;
        });
    }
}
</script>
