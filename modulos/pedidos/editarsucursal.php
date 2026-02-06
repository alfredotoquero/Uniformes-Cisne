<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");

$claseSucursales = new Sucursales();

?>
<div style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Editar Sucursal Pedido: #<?= $_GET["idpedido"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formEditarSucursal" name="formEditarSucursal">
        <input type="hidden" name="controlador" id="controlador" value="pedidos">
        <input type="hidden" name="accion" id="accion" value="editarsucursal">
        <input type="hidden" name="href" id="href" value="/pedidos">
        <input type="hidden" name="idpedido" value="<? echo $_GET["idpedido"]; ?>">
        <div class="mb-3">
            <label for="slcSucursal" class="form-label">Sucursal</label>
            <select name="slcSucursal" id="slcSucursal" class="form-control">
                <option value="0">--Selecciona una Sucursal--</option>
                <?
                $sucursales = $claseSucursales->obtenerSucursales($_POST);
                foreach ($sucursales["sucursales"] as $sucursal) {
                ?>
                    <option value="<?= $sucursal["idsucursal"]; ?>" <? if ($_GET["idsucursal"] == $sucursal["idsucursal"]) { ?>selected<? } ?>><?= $sucursal["nombre"]; ?></option>
                <?
                }
                ?>
            </select>
        </div>
        <button type="button" onclick="validarFormulario('formEditarSucursal');" class="btn btn-primary">Guardar</button>
    </form>
</div>