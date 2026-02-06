<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Contactos.php");

$claseUsuarios = new Usuarios();
$claseContactos = new Contactos();

?>
<div class="box-body">
    <div class="mt-3 mb-3" style="text-align: right;">
        <button type="button" onclick="toggleDiv('formTareas','btnAgregarT')" class="btn btn-primary" id="btnAgregarT">Agregar</button>
    </div>
    <form name="formTareas" id="formTareas" style="display:none;">
        <input type="hidden" name="controlador" id="controlador" value="clientes">
        <input type="hidden" name="accion" id="accion" value="agregartarea">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/tareas/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divTareas">
        <input type="hidden" name="idseguimiento" id="idseguimiento" value="<?= 0// $_POST["idseguimiento"] ?>">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
        <div class="mt-3 mb-3">
            <label for="txtTitulo" class="form-label">Título<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtTitulo" id="txtTitulo" placeholder="Ingresa el Título" autocomplete="off" data-mensajeerror="Debes indicar un título">
        </div>
        <div class="mb-3">
            <label for="txtComentarios" class="form-label">Comentarios<span>*</span></label>
            <textarea name="txtComentarios" id="txtComentarios" rows="5" class="form-control" autocomplete="off"></textarea>
        </div>
        <div class="mb-3">
            <label for="txtFecha" class="form-label">Fecha<span>*</span></label>
            <input type="text" name="txtFecha" id="txtFecha" rows="5" class="form-control requerido date" autocomplete="off" data-mensajeerror="Debes indicar una fecha">
        </div>
        <?
        if ($claseUsuarios->esAdministrador($_SESSION["usuario"]["idusuario"])) {
            ?>
            <div class="mb-3">
                <label for="slcUsuario" class="form-label">Usuario<span></span></label>
                <select name="slcUsuario" id="slcUsuario" class="form-control">
                    <option value="0">--Seleccionar--</option>
                    <?
                    $usuarios = $claseUsuarios->obtenerUsuarios($_POST);
                    foreach ($usuarios["usuarios"] as $usuario) {
                        ?>
                        <option value="<?= $usuario["idusuario"] ?>"><?= $usuario["nombre"] ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <?
        }
        ?>
        <button type="button" onclick="validarFormulario('formTareas');" class="btn btn-primary">Guardar</button>
        <button type="button" onclick="cancelarFormulario('formTareas','btnAgregarT')" class="btn btn-danger">Cancelar</button>
        <hr>
    </form>

    <div class="mb-3 bg-light p-3 rounded">
        <form id="formBusqueda2" name="formBusqueda2">
            <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/tareas/lista.php">
            <input type="hidden" name="contenedor" id="contenedor" value="divTareas">
            <!-- <input type="hidden" name="pagina" id="pagina" value="1"> -->
            <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
            <div class="row">
                <div class="col-12 col-md-3">
                    <select name="slcOrden" id="slcOrden" class="form-control">
                        <option value="0">Ordenar:</option>
                        <option value="1">Alfabéticamente</option>
                        <option value="2">Por fecha</option>
                        <option value="3">Por fecha de registro</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <a href="javascript:;" onclick="cargarDatosContenedor('formBusqueda2');" class="btn btn-secondary btn-sm"><i class="uil uil-search-alt me-1"></i>Filtrar</a>
                    <a href="javascript:;" onclick="limpiarFormulario('formBusqueda2');" class="btn btn-warning btn-sm"><i class="uil uil-refresh me-1"></i>Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div id="divTareas"></div>
</div>

<script>
    $(document).ready(function (e) {
        cargarDatosContenedor("formTareas");
        
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
                orientation: "bottom",
                startDate: output
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