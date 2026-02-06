<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Colores.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Tallas.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");

$claseColores = new Colores();
$colores = $claseColores->obtenerColores($_POST);

$claseTallas = new Tallas();
$_POST["portipotalla"] = 1;
$tallas = $claseTallas->obtenerTallas($_POST);

$_POST["idproducto"] = $_GET["idproducto"];

$claseProductos = new Productos();
$coloresp = $claseProductos->obtenerColoresProducto($_POST);
$tallasp = $claseProductos->obtenerTallasProducto($_POST);



unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div style="width:800px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Asignar Colores y Tallas</h4>
        </div>
    </div>
    <hr>
    <form id="formColoresTallas" name="formColoresTallas">
        <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="idproducto" id="idproducto" value="<?= $_GET["idproducto"] ?>">
        <input type="hidden" name="accion" id="accion" value="asignarcoloresytallas">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="slcColor" class="form-label">Color</label>
                    <select name="slcColor" id="slcColor" class="form-control select2" onChange="agregarColor(this.value);">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($colores["colores"] as $color) {
                            $_POST["idcolor"] = $color["idcolor"];
                        ?>
                            <option value="<?= $color["idcolor"] . "-" . $color["nombre"] ?>" <? if ($claseProductos->tieneColorAsignado($_POST)) { ?> disabled <? } ?>><?= $color["nombre"] ?></option>
                        <?
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="listaColores">
                        <table class="table m-0 table-striped" id="tablaColores">
                            <!-- <p style="margin-top: 15px; display: none;">Lista de colores seleccionados para este producto</p> -->
                            <thead <? if ($coloresp["respuesta"] != "OK") { ?>style="display:none;" <? } ?>>
                                <tr>
                                    <th>Nombre</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                if ($coloresp["respuesta"] == "OK") {
                                    $i = 1;
                                    foreach ($coloresp["colores"] as $color) {
                                ?>
                                        <tr id="color<?= $i ?>">
                                            <input type="hidden" name="idcolores[]" value="<?= $color["idcolor"] ?>">
                                            <td><?= $color["color"] ?></td>
                                            <td align="right"><a href="javascript:;" onClick="eliminarColor(<?= $i; ?>,'<?= $color["idcolor"] . "-" . $color["color"] ?>');"><i class="uil uil-times"></i></a></td>
                                        </tr>
                                <?
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="slcTalla" class="form-label">Talla</label>
                    <select name="slcTalla" id="slcTalla" class="form-control select2" onChange="agregarTalla(this.value);">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($tallas["tallas"] as $talla) {
                            $_POST["idtalla"] = $talla["idtalla"];
                        ?>
                            <option value="<?= $talla["idtalla"] . "-" . $talla["nombre"] ?>" <? if ($claseProductos->tieneTallaAsignada($_POST)) { ?> disabled <? } ?>><?= $talla["nombre"] ?></option>
                        <?
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">


                    <div class="listaTallas">
                        <!-- <p style="margin-top: 15px;">Lista de tallas seleccionadas para este producto</p> -->
                        <table class="table m-0 table-striped" id="tablaTallas">
                            <thead <? if ($tallasp["respuesta"] != "OK") { ?>style="display:none;" <? } ?>>
                                <tr>
                                    <th>Nombre</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                if ($tallasp["respuesta"] == "OK") {
                                    $i = 1;
                                    foreach ($tallasp["tallas"] as $talla) {
                                ?>
                                        <tr id="talla<?= $i ?>">
                                            <input type="hidden" name="idtallas[]" id="idtalla<?= $talla["idtalla"] ?>" value="<?= $talla["idtalla"] ?>">
                                            <td><?= $talla["talla"] ?></td>
                                            <td align="right"><a href="javascript:;" onClick="eliminarTalla(<?= $i ?>,'<?= $talla["idtalla"] . "-" . $talla["talla"]; ?>');"><i class="uil uil-times"></i></a></td>
                                        </tr>
                                <?
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- </div> -->
        <center><button type="button" onclick="validarFormulario('formColoresTallas');" class="btn btn-primary">Guardar</button></center>
    </form>
</div>

<script>
    $(document).ready(function(e) {
        $(".select2").select2();
    });

    function agregarColor(idcolor) {
        console.log("idcolor: " + idcolor);

        var value = idcolor;
        var datos = idcolor.split("-");

        $("#slcColor option[value='" + idcolor + "']").attr('disabled', 'disabled');

        var idcolor = datos[0];
        var nombre = datos[1];

        var rowCount = $('#tablaColores tr').length;

        console.log("valor seleccionado: " + value);

        // agregar el elemento a la tabla
        $('#tablaColores > tbody:last-child').append('\
        <tr id="color' + rowCount + '">\
            <input type="hidden" name="idcolores[]" value="' + idcolor + '">\
            <td class="align-middle text-right">' + nombre + '</td>\
            <td align="right">\
                <a href="javascript:;" onclick="eliminarColor(' + rowCount + ',\'' + value + '\');" ><i class="uil uil-times"></i></a>\
            </td>\
        </tr>\
        ');

        $("#slcColor").val($("#slcColor option:first").val());
        $("#slcColor").select2();

        $("#tablaColores thead").show();
    }

    function agregarTalla(idtalla) {
        console.log("idtalla: " + idtalla);

        var value = idtalla;
        var datos = idtalla.split("-");

        $("#slcTalla option[value='" + idtalla + "']").attr('disabled', 'disabled');

        var idtalla = datos[0];
        var nombre = datos[1];

        var rowCount = $('#tablaTallas tr').length;

        console.log("valor seleccionado: " + value);

        // agregar el elemento a la tabla
        $('#tablaTallas > tbody:last-child').append('\
        <tr id="talla' + rowCount + '">\
            <input type="hidden" name="idtallas[]" value="' + idtalla + '">\
            <td class="align-middle text-right">' + nombre + '</td>\
            <td align="right">\
                <a href="javascript:;" onclick="eliminarTalla(' + rowCount + ',\'' + value + '\');" ><i class="uil uil-times"></i></a>\
            </td>\
        </tr>\
        ');

        $("#slcTalla").val($("#slcTalla option:first").val());
        $("#slcTalla").select2();

        $("#tablaTallas thead").show();
    }

    function eliminarColor(idcolor, value) {
        console.log("idcolor eliminado: " + idcolor);

        $('#color' + idcolor).remove();

        $("#slcColor option[value='" + value + "']").removeAttr('disabled');
        $("#slcColor").select2();

        if ($('#tablaColores tr').length == 1) {
            $("#tablaColores thead").hide();
        }

    }

    function eliminarTalla(idtalla, value) {
        console.log("idtalla eliminada: " + idtalla);
        console.log("value: " + value);

        $('#talla' + idtalla).remove();

        $("#slcTalla option[value='" + value + "']").removeAttr('disabled');
        $("#slcTalla").select2();

        if ($('#tablaTallas tr').length == 1) {
            $("#tablaTallas thead").hide();
        }
    }
</script>