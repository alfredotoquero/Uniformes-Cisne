<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$claseAlmacenes = new Almacenes();
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST)["almacenes"];
$almacenes_reorden = $claseAlmacenes->obtenerAlmacenesReorden()["almacenes"];

$claseTiendas = new Tiendas();
$tiendas = $claseTiendas->obtenerTiendas(array())["tiendas"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Sucursal</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="sucursales">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="slcTienda" class="form-label">Tienda<span>*</span></label>
            <select class="form-control requerido" name="slcTienda" id="slcTienda" data-mensajeerror="Debes indicar la tienda">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($tiendas as $tienda) {
                    ?>
                    <option value="<?= $tienda["idtienda"]; ?>"><?= $tienda["nombre"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcAlmacen" class="form-label">Almacen<span>*</span></label>
            <select class="form-control requerido" name="slcAlmacen" id="slcAlmacen" data-mensajeerror="Debes indicar el almacen">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($almacenes as $almacen) {
                    ?>
                    <option value="<?= $almacen["idalmacen"]; ?>"><?= $almacen["nombre"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcAlmacenReorden" class="form-label">Almacen de reorden</label>
            <select class="form-control" name="slcAlmacenReorden" id="slcAlmacenReorden">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($almacenes_reorden as $almacen) {
                    ?>
                    <option value="<?= $almacen["idalmacen"]; ?>"><?= $almacen["nombre"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcAlmacenProduccion" class="form-label">Almacen de producción</label>
            <select class="form-control" name="slcAlmacenProduccion" id="slcAlmacenProduccion">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($almacenes as $almacen) {
                    ?>
                    <option value="<?= $almacen["idalmacen"]; ?>"><?= $almacen["nombre"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="txtRazonSocial" class="form-label">Razón Social</label>
            <input type="text" class="form-control" name="txtRazonSocial" id="txtRazonSocial">
        </div>
        <div class="mb-3">
            <label for="txtRFC" class="form-label">RFC</label>
            <input type="text" class="form-control" name="txtRFC" id="txtRFC">
        </div>
        <div class="mb-3">
            <label for="txtCalle" class="form-label">Calle</label>
            <input type="text" class="form-control" name="txtCalle" id="txtCalle">
        </div>
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="txtNumero" class="form-label">Número Ext.</label>
                    <input type="text" class="form-control" name="txtNumero" id="txtNumero">
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="txtNumeroInt" class="form-label">Número Int.</label>
                    <input type="text" class="form-control" name="txtNumeroInt" id="txtNumeroInt">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="txtColonia" class="form-label">Colonia</label>
            <input type="text" class="form-control" name="txtColonia" id="txtColonia">
        </div>
        <div class="mb-3">
            <label for="txtCiudad" class="form-label">Ciudad</label>
            <input type="text" class="form-control" name="txtCiudad" id="txtCiudad">
        </div>
        <div class="mb-3">
            <label for="txtEstado" class="form-label">Estado</label>
            <input type="text" class="form-control" name="txtEstado" id="txtEstado">
        </div>
        <div class="mb-3">
            <label for="txtCodigoPostal" class="form-label">Código Postal</label>
            <input type="text" class="form-control" name="txtCodigoPostal" id="txtCodigoPostal">
        </div>
        <div class="mb-3">
            <label for="txtTelefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" name="txtTelefono" id="txtTelefono">
        </div>
        <div class="mb-3">
            <label for="txtRegimen" class="form-label">Régimen Fiscal</label>
            <input type="text" class="form-control" name="txtRegimen" id="txtRegimen">
        </div>
        <div class="mb-3">
            <label for="imgTicket" class="form-label">Imagen Ticket</label>
            <input type="file" class="form-control" name="imgTicket" id="imgTicket">
        </div>
        <div class="mb-3">
            <label for="imgFormato" class="form-label">Plantilla de documentos</label>
            <input type="file" class="form-control" name="imgFormato" id="imgFormato">
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>