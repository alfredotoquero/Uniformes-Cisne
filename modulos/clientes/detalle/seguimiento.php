<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Contactos.php");

$claseUsuarios = new Usuarios();
$claseClientes = new Clientes();
$claseContactos = new Contactos();

?>
<div class="box-body">
    <div class="row">
        <div class="col-12">
            <div class="mt-3 mb-3" style="text-align: right;">
                <button type="button" onclick="toggleDiv('formSeguimiento','btnAgregarS')" class="btn btn-primary" id="btnAgregarS">Agregar</button>
            </div>
            <form name="formSeguimiento" id="formSeguimiento" style="display:none;">
                <input type="hidden" name="controlador" id="controlador" value="clientes">
                <input type="hidden" name="accion" id="accion" value="agregarseguimiento">
                <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/seguimiento/lista.php">
                <input type="hidden" name="contenedor" id="contenedor" value="divSeguimiento">
                <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
                <div class="mt-3 mb-3">
                    <label for="txtTitulo" class="form-label">Título<span>*</span></label>
                    <input type="text" class="form-control requerido" name="txtTitulo" id="txtTitulo" placeholder="Ingresa el Título" autocomplete="off" data-mensajeerror="Debes indicar un Titulo">
                </div>
                <div class="mb-3">
                    <label for="txtComentarios" class="form-label">Comentarios<span>*</span></label>
                    <textarea name="txtComentarios" id="txtComentarios" rows="5" class="form-control" autocomplete="off"></textarea>
                </div>
                <div class="mb-3">
                    <label for="txtFecha" class="form-label">Fecha de recordatorio</label>
                    <input type="text" name="txtFecha" id="txtFecha" rows="5" class="form-control date" autocomplete="off">
                </div>
                <?
                if ($claseUsuarios->esAdministrador($_SESSION["usuario"]["idusuario"])) {
                ?>
                    <div class="mb-3">
                        <label for="slcUsuario" class="form-label">Asignar recordatorio a:<span></span></label>
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

                <div class="mb-3">
                    <label for="slcContacto" class="form-label">Contacto<span></span></label>
                    <select name="slcContacto" id="slcContacto" class="form-control">
                        <option value="0">--Seleccionar--</option>
                        <?
                        $contactos = $claseContactos->obtenerContactos($_POST);
                        foreach ($contactos["contactos"] as $contacto) {
                        ?>
                            <option value="<?= $contacto["idcontacto"] ?>"><?= $contacto["nombre"] ?></option>
                        <?
                        }
                        ?>
                    </select>
                </div>
                <button type="button" onclick="validarFormulario('formSeguimiento');" class="btn btn-primary">Guardar</button>
                <button type="button" onclick="cancelarFormulario('formSeguimiento','btnAgregarS')" class="btn btn-danger">Cancelar</button>
                <hr>
            </form>

            <form name="formComentarios" id="formComentarios" style="display: none;">
                <input type="hidden" name="controlador" id="controlador" value="clientes">
                <input type="hidden" name="accion" id="accion" value="responderseguimiento">
                <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
                <input type="hidden" name="idseguimiento" id="idseguimiento" value="">
                <div class="mb-3">
                    <label for="txtComentarios" class="form-label">Comentarios<span>*</span></label>
                    <textarea name="txtComentarios" id="txtComentarios" rows="5" class="form-control requerido" autocomplete="off" data-mensajeerror="Debes indicar comentarios"></textarea>
                </div>
                <div class="mb-3">
                    <label for="txtFecha" class="form-label">Fecha<span></span></label>
                    <input type="text" name="txtFecha" id="txtFecha" rows="5" class="form-control date" autocomplete="off">
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
                <button type="button" onclick="validarFormulario('formComentarios');" class="btn btn-primary">Guardar</button>
                <button type="button" onclick="cancelarFormulario('formComentarios','btnAgregarS')" class="btn btn-danger">Cancelar</button>
                <hr>
            </form>

            <div id="divSeguimiento"></div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(e) {
        cargarDatosContenedor("formSeguimiento");
        cargarDatosContenedor("formTareas");

        if ($(".date").length) {
            var d = new Date();

            var month = d.getMonth() + 1;
            var day = d.getDate();

            var output = d.getFullYear() + '-' +
                (('' + month).length < 2 ? '0' : '') + month + '-' +
                (('' + day).length < 2 ? '0' : '') + day;

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
    });
</script>