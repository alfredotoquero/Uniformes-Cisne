<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Contactos.php");

$claseClientes = new Clientes();
$claseContactos = new Contactos();

// $_POST["idcliente"] = $_POST["idcliente"];
$cliente = $claseClientes->obtenerCliente($_POST)["cliente"];

?>
<div class="box-body">
    <h4>Datos Generales</h4>

    <div class="row mt-3">
        <div class="col-xs-12 col-md-6"><b>Nombre:</b> <?= $cliente["nombre"]; ?></div>
        <div class="col-xs-12 col-md-6"><b>Correo:</b> <?= $cliente["correo"]; ?></div>
    </div>
    <div class="row mb-3">
        <div class="col-xs-12 col-md-6"><b>Telefono:</b> <?= $cliente["telefono"]; ?></div>
        <div class="col-xs-12 col-md-6"><b>Saldo:</b> $<?= number_format(0, 2); ?></div>
    </div>
    <hr>

    <h4>Contactos</h4>

    <div class="mt-3 mb-3" style="text-align: right;">
        <button type="button" onclick="toggleDiv('formContactos','btnAgregarC')" class="btn btn-primary" id="btnAgregarC">Agregar</button>
    </div>
    <form name="formContactos" id="formContactos" style="display:none;">
        <input type="hidden" name="controlador" id="controlador" value="contactos">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/clientes/detalle/contactos/lista.php">
        <input type="hidden" name="contenedor" id="contenedor" value="divContactos">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_POST["idcliente"] ?>">
        <input type="hidden" name="idcontacto" id="idcontacto" value="">
        <div class="mb-3">
            <label for="txtContacto" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtContacto" id="txtContacto" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="txtPuesto" class="form-label">Puesto<span></span></label>
            <input type="text" class="form-control" name="txtPuesto" id="txtPuesto" placeholder="Ingresa el puesto" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="txtCorreo" class="form-label">Correo electrónico<span></span></label>
            <input type="email" class="form-control" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar un correo electrónico válido">
        </div>
        <div class="mb-3">
            <label for="txtTelefono" class="form-label">Teléfono<span></span></label>
            <input type="tel" class="form-control" name="txtTelefono" id="txtTelefono" placeholder="Ingresa el teléfono" autocomplete="off" data-mensajeerror="Debes indicar un teléfono válido">
        </div>
        <button type="button" onclick="validarFormulario('formContactos');" class="btn btn-primary">Guardar</button>
        <button type="button" onclick="cancelarFormulario('formContactos','btnAgregarC')" class="btn btn-danger">Cancelar</button>
        <hr>
    </form>

    <div id="divContactos"></div>
</div>
<script>
    $(document).ready(function(e) {
        cargarDatosContenedor("formContactos");
    });
</script>