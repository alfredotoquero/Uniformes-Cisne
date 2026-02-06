<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$claseClientes = new Clientes();
$claseTiendas = new Tiendas();

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Agregar Cliente</h4>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="clientes">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <div class="mb-3">
            <label for="txtNombre" class="form-label">Nombre Comercial<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtNombre" id="txtNombre" placeholder="Ingresa el nombre" autocomplete="off" data-mensajeerror="Debes indicar el nombre">
        </div>
        <div class="mb-3">
            <label for="txtTelefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" name="txtTelefono" id="txtTelefono" placeholder="Ingresa el teléfono" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="txtCorreo" class="form-label">Correo Electrónico Facturación</label>
            <input type="email" class="form-control" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="slcCiudad" class="form-label">Ciudad</label>
            <select name="slcCiudad" id="slcCiudad" class="form-control">
                <option value="0">--Seleccionar--</option>
                <option value="1">Todas las ciudades</option>
                <?
                $ciudades = $claseClientes->obtenerCiudades();
                foreach ($ciudades["ciudades"] as $ciudad) {
                    ?>
                    <option value="<?= $ciudad["idciudad"] ?>"><?= $ciudad["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="slcTienda" class="form-label">Tienda</label>
            <select name="slcTienda" id="slcTienda" class="form-control">
                <option value="0">--Seleccionar--</option>
                <?
                $tiendas = $claseTiendas->obtenerTiendas(array());
                foreach ($tiendas["tiendas"] as $tienda) {
                    ?>
                    <option value="<?= $tienda["idtienda"] ?>"><?= $tienda["nombre"] ?></option>
                    <?
                }
                ?>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar</button>
    </form>
</div>