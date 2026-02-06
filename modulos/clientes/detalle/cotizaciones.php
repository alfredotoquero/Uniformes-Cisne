<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$claseCotizaciones = new Cotizaciones();

$cotizaciones = $claseCotizaciones->obtenerCotizaciones($_POST);
?>
<div class="box-body pt-3">
    <div class="mb-3 bg-light p-3 rounded">
        <form id="formBusqueda2" name="formBusqueda2">
            <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/cotizaciones/lista.php">
            <input type="hidden" name="contenedor" id="contenedor" value="divListaCotizaciones">
            <input type="hidden" name="pagina" id="pagina" value="1">
            <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
            <div class="row">
                <div class="col-12 col-md-3">
                    <input type="text" class="form-control date" name="txtFechaInicial" id="txtFechaInicial" placeholder="Fecha Inicial" autocomplete="off">
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" class="form-control date" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha Final" autocomplete="off">
                </div>
                <div class="col-12 col-md-auto">
                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda2');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda2');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div id="divListaCotizaciones"></div>
</div>

<script>
    $(document).ready(function (e) {
        cargarDatosContenedor("formBusqueda2");

        if ($(".date").length) {
            var d = new Date();

            var month = d.getMonth()+1;
            var day = d.getDate();

            var output = d.getFullYear() + '-' +
                ((''+month).length<2 ? '0' : '') + month + '-' +
                ((''+day).length<2 ? '0' : '') + day;

            $(".date").datepicker({
                format: "yyyy-mm-dd",
                showOtherMonths: true,
                selectOtherMonths: true,
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                orientation: "bottom"
            });
        }

        $("#formBusqueda2 :input[type=text]").on("keypress", function (e) {
            if(e.keyCode === 13) {
                console.log("entra filtrar");
                e.preventDefault();
                cargarDatosContenedor($(this.form).attr("name"));
            }
        });

        $("#formBusqueda2 select").on("change", function (e) {
            console.log("entra filtrar");
            e.preventDefault();
            cargarDatosContenedor($(this.form).attr("name"));
        });
    });
</script>