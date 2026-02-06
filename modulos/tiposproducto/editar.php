<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TiposProducto.php");

$_POST["idtipoproducto"] = $_GET["idtipoproducto"];

$claseTiposProducto = new TiposProducto();
$tipoproducto = $claseTiposProducto->obtenerTipoProducto($_POST)["tipoproducto"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Tipo de Producto</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="tiposproducto">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idtipoproducto" id="idtipoproducto" value="<?= $_GET["idtipoproducto"] ?>">
        <input type="hidden" id="txtUnidadMedidaID" value="<? echo $tipoproducto["idunidadmedida"]; ?>">
        <input type="hidden" id="txtUnidadMedidaDescripcion" value="<? echo $tipoproducto["descripcion_unidadmedida"]; ?>">
        <input type="hidden" id="txtProductoServicioID" value="<? echo $tipoproducto["idproductoservicio"]; ?>">
        <input type="hidden" id="txtProductoServicioDescripcion" value="<? echo $tipoproducto["descripcion_productoservicio"]; ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $tipoproducto["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="slcProductoServicio" class="form-label">Producto/Servicio SAT<span>*</span></label>
            <select name="slcProductoServicio" id="slcProductoServicio" class="form-control requerido" data-mensajeerror="Debes indicar el producto/servicio SAT">
                <option value="0">--Selecciona el producto/servicio--</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcUnidadMedida" class="form-label">Unidad de Medida SAT<span>*</span></label>
            <select name="slcUnidadMedida" id="slcUnidadMedida" class="form-control requerido" data-mensajeerror="Debes indicar la unidad de medida SAT">
                <option value="0">--Selecciona la unidad de medida--</option>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>
<script>
$(document).ready(function(e){
    $("#slcProductoServicio").select2({
        minimumInputLength: 4,
        ajax: {
            url: "/assets/php/controladores/sat.php",
            type: "POST",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    palabraClave: params.term,
                    accion: "obtenerProductosServicios"
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.productosservicios, function(obj) {
                        return {
                            id: obj.idproductoservicio,
                            text: obj.clave + ' - ' + obj.descripcion
                        };
                    })
                };
            },
            cache: true
        }
    });

    $("#slcUnidadMedida").select2({
        minimumInputLength: 1,
        ajax: {
            url: "/assets/php/controladores/sat.php",
            type: "POST",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    palabraClave: params.term,
                    accion: "obtenerUnidadesMedida"
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.unidadesmedida, function(obj) {
                        return {
                            id: obj.idunidadmedida,
                            text: obj.clave + ' - ' + obj.nombre
                        };
                    })
                };
            },
            cache: true
        }
    });

    if($("#txtUnidadMedidaID").val()>0){
        var option = new Option($("#txtUnidadMedidaDescripcion").val(), $("#txtUnidadMedidaID").val(), true, true);
        $('#slcUnidadMedida').append(option).trigger('change');
    }
    if($("#txtProductoServicioID").val()>0){
        var option = new Option($("#txtProductoServicioDescripcion").val(), $("#txtProductoServicioID").val(), true, true);
        $('#slcProductoServicio').append(option).trigger('change');
    }
});
</script>