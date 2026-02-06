<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$claseAlmacenes = new Almacenes();
$almacenes = $claseAlmacenes->obtenerAlmacenes($_POST)["almacenes"];
$almacenes_reorden = $claseAlmacenes->obtenerAlmacenesReorden()["almacenes"];

$claseTiendas = new Tiendas();
$tiendas = $claseTiendas->obtenerTiendas($_POST)["tiendas"];

$_POST["idsucursal"] = $_GET["idsucursal"];

$claseSucursales = new Sucursales();
$sucursal = $claseSucursales->obtenerSucursal($_POST)["sucursal"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Sucursal</h4>
        </div>
    </div>
    <hr>
    <form id="formEditar" name="formEditar">
        <input type="hidden" name="controlador" id="controlador" value="sucursales">
        <input type="hidden" name="accion" id="accion" value="editar">
        <input type="hidden" name="idsucursal" id="idsucursal" value="<?= $_GET["idsucursal"] ?>">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre" value="<?= $sucursal["nombre"] ?>">
        </div>
        <div class="mb-3">
            <label for="slcTienda" class="form-label">Tienda<span>*</span></label>
            <select class="form-control requerido" name="slcTienda" id="slcTienda" data-mensajeerror="Debes indicar la tienda">
                <option value="0">--Seleccionar--</option>
                <?
                foreach ($tiendas as $tienda) {
                    ?>
                    <option value="<?= $tienda["idtienda"]; ?>" <? if($tienda["idtienda"]==$sucursal["idtienda"]){?> selected <?} ?>><?= $tienda["nombre"]; ?></option>
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
                    <option value="<?= $almacen["idalmacen"]; ?>" <? if($almacen["idalmacen"]==$sucursal["idalmacen"]){?> selected <?} ?>><?= $almacen["nombre"]; ?></option>
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
                    <option value="<?= $almacen["idalmacen"]; ?>" <?= ($sucursal["idalmacen_reorden"]==$almacen["idalmacen"]) ? "selected" : "" ?>><?= $almacen["nombre"]; ?></option>
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
                    <option value="<?= $almacen["idalmacen"]; ?>" <? if($almacen["idalmacen"]==$sucursal["idalmacen_produccion"]){?> selected <?} ?>><?= $almacen["nombre"]; ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="txtRazonSocial" class="form-label">Razón Social</label>
            <input type="text" class="form-control" name="txtRazonSocial" id="txtRazonSocial" value="<?= $sucursal["razonsocial"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtRFC" class="form-label">RFC</label>
            <input type="text" class="form-control" name="txtRFC" id="txtRFC" value="<?= $sucursal["rfc"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtCalle" class="form-label">Calle</label>
            <input type="text" class="form-control" name="txtCalle" id="txtCalle" value="<?= $sucursal["calle"] ?>">
        </div>
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="txtNumero" class="form-label">Número Ext.</label>
                    <input type="text" class="form-control" name="txtNumero" id="txtNumero" value="<?= $sucursal["numero"] ?>">
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="txtNumeroInt" class="form-label">Número Int.</label>
                    <input type="text" class="form-control" name="txtNumeroInt" id="txtNumeroInt" value="<?= $sucursal["numeroint"] ?>">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="txtColonia" class="form-label">Colonia</label>
            <input type="text" class="form-control" name="txtColonia" id="txtColonia" value="<?= $sucursal["colonia"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtCiudad" class="form-label">Ciudad</label>
            <input type="text" class="form-control" name="txtCiudad" id="txtCiudad" value="<?= $sucursal["ciudad"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtEstado" class="form-label">Estado</label>
            <input type="text" class="form-control" name="txtEstado" id="txtEstado" value="<?= $sucursal["estado"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtCodigoPostal" class="form-label">Código Postal</label>
            <input type="text" class="form-control" name="txtCodigoPostal" id="txtCodigoPostal" value="<?= $sucursal["codigopostal"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtTelefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" name="txtTelefono" id="txtTelefono" value="<?= $sucursal["telefono"] ?>">
        </div>
        <div class="mb-3">
            <label for="txtRegimen" class="form-label">Régimen Fiscal</label>
            <input type="text" class="form-control" name="txtRegimen" id="txtRegimen" value="<?= $sucursal["regimen"] ?>">
        </div>
        <div class="mb-3">
            <label for="imgTicket" class="form-label">Imagen Ticket</label>
            <div class="row">
                <div class="col-10">
                    <input type="file" class="form-control" name="imgTicket" id="imgTicket">

                </div>
                <div class="col-2">
                    <?
                    if(file_exists($_SERVER["DOCUMENT_ROOT"]."/../imagenes/sucursales/".$sucursal["imagen"]) and $sucursal["imagen"]!=""){
                        ?>
                        <img src="https://uniformescisne.mx/imagenes/sucursales/<? echo $sucursal["imagen"]; ?>" class="img-responsive" width="100%">
                        <?
                    }
                    ?>
                </div>
            </div>
            <!-- mostrar la imagen si subieron una -->
        </div>
        <div class="mb-3">
            <label for="imgFormato" class="form-label">Plantilla de documentos</label>
            <div class="row">
                <div class="col-10">
                    <input type="file" class="form-control" name="imgFormato" id="imgFormato">

                </div>
                <div class="col-2">
                    <?
                    if(file_exists($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$sucursal["formato"]) and $sucursal["formato"]!=""){
                        ?>
                        <img src="/assets/images/formatos/<? echo $sucursal["formato"]; ?>" class="img-responsive" width="100%">
                        <?
                    }
                    ?>
                </div>
            </div>
            <!-- mostrar la imagen si subieron una -->
        </div>
        <button type="button" onclick="validarFormulario('formEditar');" class="btn btn-primary">Guardar</button>
    </form>
</div>