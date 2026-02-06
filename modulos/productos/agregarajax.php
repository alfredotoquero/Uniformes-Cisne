<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseProveedores = new Proveedores();
$claseTiposProducto = new TiposProducto();
$claseAlmacenes = new Almacenes();
$claseColores = new Colores();
$claseTallas = new Tallas();

$proveedores = $claseProveedores->obtenerProveedores($_POST);
$tiposproducto = $claseTiposProducto->obtenerTiposProducto($_POST);
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
$colores = $claseColores->obtenerColores($_POST);

$_POST["portipotalla"] = 1;
$tallas = $claseTallas->obtenerTallas($_POST);

$claseProductos = new Productos();
$codigobarras = $claseProductos->obtenerSiguienteCodigoBarras($_POST)["codigobarras"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Producto</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="productos">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="slcTipoProducto" class="form-label">Tipo de Producto<span>*</span></label>
            <select name="slcTipoProducto" id="slcTipoProducto" class="form-control requerido" data-mensajeerror="Debes indicar el tipo de producto">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($tiposproducto["tiposproducto"] as $tipoproducto) {
                    ?>
                    <option value="<?= $tipoproducto["idtipoproducto"] ?>"><?= $tipoproducto["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcProveedor" class="form-label">Proveedor<span>*</span></label>
            <select name="slcProveedor" id="slcProveedor" class="form-control requerido" data-mensajeerror="Debes indicar el proveedor">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($proveedores["proveedores"] as $proveedor) {
                    ?>
                    <option value="<?= $proveedor["idproveedor"] ?>"><?= $proveedor["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="txtClaveProveedor" class="form-label">Clave Proveedor</label>
            <input type="text" class="form-control" name="txtClaveProveedor" id="txtClaveProveedor" placeholder="Ingresa la clave" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Tipo<span>*</span></label>
            <input type="radio" name="slcTipo" id="slcTipo1" value="P" class="requerido" data-mensajeerror="Debes indicar un tipo">&nbsp;&nbsp;Producto 
            <input type="radio" name="slcTipo" id="slcTipo2" value="S" class="requerido" data-mensajeerror="Debes indicar un tipo">&nbsp;&nbsp;Servicio
        </div>

        <div class="mb-3">
            <label for="chkAlmacenes" class="form-label">Almacenes<span>*</span></label>
            <?
            foreach ($almacenes["almacenes"] as $almacen) {
                $_POST["idalmacen"] = $almacen["idalmacen"];
                ?>
                <div class="row">
                    <div class="col-6">
                        <?= $almacen["nombre"] ?>
                    </div>
                    <div class="col-6">
                        <input type="checkbox" name="chkAlmacenes[]" id="chkAlmacen<?= $almacen["idalmacen"] ?>" data-switch="none" value="<?= $almacen["idalmacen"]; ?>">
                        <label for="chkAlmacen<?= $almacen["idalmacen"] ?>" data-on-label="" data-off-label=""></label>
                    </div>
                </div>
                <?
            }
            ?>
        </div>

        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="slcColorP" class="form-label">Color</label>
                    <select name="slcColorP" id="slcColorP" class="form-control select2p" onChange="agregarColor(this.value);">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($colores["colores"] as $color) {
                            $_POST["idcolor"] = $color["idcolor"];
                            ?>
                            <option value="<?= $color["idcolor"] . "-" . $color["nombre"] ?>" ><?= $color["nombre"] ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="listaColores">
                        <table class="table m-0 table-striped" id="tablaColores">
                            <!-- <p style="margin-top: 15px; display: none;">Lista de colores seleccionados para este producto</p> -->
                            <thead style="display:none;">
                                <tr>
                                    <th>Nombre</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="slcTallaP" class="form-label">Talla</label>
                    <select name="slcTallaP" id="slcTallaP" class="form-control select2p" onChange="agregarTalla(this.value);">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($tallas["tallas"] as $talla) {
                            $_POST["idtalla"] = $talla["idtalla"];
                            ?>
                            <option value="<?= $talla["idtalla"] . "-" . $talla["nombre"] ?>" ><?= $talla["nombre"] ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                

                    <div class="listaTallas">
                    <!-- <p style="margin-top: 15px;">Lista de tallas seleccionadas para este producto</p> -->
                        <table class="table m-0 table-striped" id="tablaTallas">
                            <thead style="display:none;">
                                <tr>
                                    <th>Nombre</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="txtNombre" class="form-label">Tienda</label>
            <input type="radio" name="rdTienda" id="rdTienda1" value="1" onClick="cambiarDiv2(this.value,'divPrecio');">&nbsp;&nbsp;Sí
            <input type="radio" name="rdTienda" id="rdTienda1" value="2" onClick="cambiarDiv2(this.value,'divPrecio');">&nbsp;&nbsp;No
        </div>
        <div id="divPrecio" style="display:none;">
        <!-- son requeridos pero solo si son visibles -->
            <div class="mb-3">
                <label for="txtPrecio" class="form-label">Precio<span>*</span></label>
                <input type="text" class="form-control" name="txtPrecio" id="txtPrecio" placeholder="Ingresa el precio" autocomplete="off" data-mensajeerror="Debes indicar el precio" disabled>
            </div>
            <div class="mb-3">
                <label for="txtCodigo" class="form-label">Código de Barras<span>*</span></label>
                <input type="text" class="form-control" name="txtCodigo" id="txtCodigo" placeholder="<?= $codigobarras ?>" autocomplete="off" data-mensajeerror="Debes indicar el código de barras" disabled>
            </div>
            <div class="mb-3">
                <label for="txtNombre" class="form-label">¿Tarjeta de Regalo?<span>*</span></label>
                <input type="radio" class="requerido" name="rdTarjetaRegalo" id="rdTarjetaRegalo1" value="0" disabled>&nbsp;&nbsp;No
                <input type="radio" class="requerido" name="rdTarjetaRegalo" id="rdTarjetaRegalo2" value="1" disabled>&nbsp;&nbsp;Sí
            </div>
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>

<script>
    $(document).ready(function (e){
        $(".select2p").select2();
    });

    function agregarColor(idcolor) {
        console.log("idcolor: " + idcolor);

        var value = idcolor;
        var datos = idcolor.split("-");

        $("#slcColorP option[value='" + idcolor + "']").attr('disabled','disabled');

        var idcolor = datos[0];
        var nombre = datos[1];

        var rowCount = $('#tablaColores tr').length;

        console.log("valor seleccionado: " + value);

        // agregar el elemento a la tabla
        $('#tablaColores > tbody:last-child').append('\
        <tr id="color'+ rowCount +'">\
            <input type="hidden" name="idcolores[]" value="' + idcolor + '">\
            <td class="align-middle text-right">' + nombre + '</td>\
            <td align="right">\
                <a href="javascript:;" onclick="eliminarColor(' + rowCount + ',\'' + value + '\');" ><i class="uil uil-times"></i></a>\
            </td>\
        </tr>\
        ');

        $("#slcColorP").val($("#slcColorP option:first").val());
        $("#slcColorP").select2();

        $("#tablaColores thead").show();
    }
    
    function agregarTalla(idtalla) {
        console.log("idtalla: " + idtalla);

        var value = idtalla;
        var datos = idtalla.split("-");

        $("#slcTallaP option[value='" + idtalla + "']").attr('disabled','disabled');

        var idtalla = datos[0];
        var nombre = datos[1];

        var rowCount = $('#tablaTallas tr').length;

        console.log("valor seleccionado: " + value);

        // agregar el elemento a la tabla
        $('#tablaTallas > tbody:last-child').append('\
        <tr id="talla'+ rowCount +'">\
            <input type="hidden" name="idtallas[]" value="' + idtalla + '">\
            <td class="align-middle text-right">' + nombre + '</td>\
            <td align="right">\
                <a href="javascript:;" onclick="eliminarTalla(' + rowCount + ',\'' + value + '\');" ><i class="uil uil-times"></i></a>\
            </td>\
        </tr>\
        ');

        $("#slcTallaP").val($("#slcTallaP option:first").val());
        $("#slcTallaP").select2();

        $("#tablaTallas thead").show();
    }
    
    function eliminarColor(idcolor,value) {
        console.log("idcolor eliminado: " + idcolor);

        $('#color' + idcolor).remove();

        $("#slcColorP option[value='" + value + "']").removeAttr('disabled');
        $("#slcColorP").select2();

        if ($('#tablaColores tr').length==1) {
            $("#tablaColores thead").hide();
        }

    }

    function eliminarTalla(idtalla,value) {
        console.log("idtalla eliminada: " + idtalla);
        console.log("value: " + value);

        $('#talla' + idtalla).remove();

        $("#slcTallaP option[value='" + value + "']").removeAttr('disabled');
        $("#slcTallaP").select2();

        if ($('#tablaTallas tr').length==1) {
            $("#tablaTallas thead").hide();
        }
    }
</script>
