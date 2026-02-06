<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$coincidencias = $claseClientes->buscarCoincidenciasClientes(array(
    "nombre" => urldecode($_GET["cliente"]),
    "correo" => $_GET["correo"],
    "telefono" => $_GET["telefono"]
));

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<div style="width:1000px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Posibles coincidencias</h4>
        </div>
        <div class="col-12">
            <small>Se detectaron posibles coincidencias con clientes registrados en el sistema</small>
        </div>
    </div>
    <hr>
    <form id="formAgregar" name="formAgregar">
        <input type="hidden" name="controlador" id="controlador" value="pedidos">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="archivo" id="archivo" value="/modulos/cotizaciones/listaproductosconvertir.php">
        <input type="hidden" name="contenedor" id="contenedor" value="listaProductos">
        <input type="hidden" name="href" id="href" value="/pedidos">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <input type="hidden" name="idusuario" id="idusuario" value="<?= $_GET["idusuario"] ?>">
        <input type="hidden" name="slcSucursal" id="slcSucursal" value="<?= $_GET["idsucursal"] ?>">
        <input type="hidden" name="chkNoGuardarCliente" id="chkNoGuardarCliente" value="<?= $_GET["noguardarcliente"] ?>">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["idcliente"] ?>">
        <input type="hidden" name="slcCliente" value="<?= urldecode($_GET["cliente"]) ?>">
        <input type="hidden" name="txtTelefono" value="<?= urldecode($_GET["telefono"]) ?>">
        <input type="hidden" name="txtCorreo" value="<?= urldecode($_GET["correo"]) ?>">
        <input type="hidden" name="slcCiudad" value="<?= $_GET["idciudad"] ?>">
        <input type="hidden" name="slcTienda" value="<?= $_GET["idtienda"] ?>">
        <input type="hidden" name="idcontacto" id="idcontacto" value="<?= $_GET["idcontacto"] ?>">
        <input type="hidden" name="slcContacto" id="slcContacto" value="<?= urldecode($_GET["contacto"]) ?>">
        <input type="hidden" name="txtCorreoC" id="txtCorreoC" value="<?= urldecode($_GET["correocontacto"]) ?>">
        <input type="hidden" name="txtTelefonoC" id="txtTelefonoC" value="<?= urldecode($_GET["telefonocontacto"]) ?>">
        <input type="hidden" name="slcVigencia" id="slcVigencia" value="<?= $_GET["opcionvigencia"] ?>">
        <input type="hidden" name="chkIncluyeIva" id="chkIncluyeIva" value="<?= $_GET["incluyeiva"] ?>">
        <input type="hidden" name="slcIVA" id="slcIVA" value="<?= $_GET["tasaiva"] ?>">
        <input type="hidden" name="chkSubtotalizar" id="chkSubtotalizar" value="<?= $_GET["subtotalizar"] ?>">
        <input type="hidden" name="subtotal" id="subtotal" value="<?= $_GET["subtotal"] ?>">
        <input type="hidden" name="iva" id="iva" value="<?= $_GET["iva"] ?>">
        <input type="hidden" name="total" id="total" value="<?= $_GET["total"] ?>">
        <input type="hidden" name="idcotizacionpadre" value="<?= $_GET["idcotizacionpadre"] ?>">
        <input type="hidden" name="txtLinea1" id="txtLinea1" value="<?= urldecode($_GET["linea1"]) ?>">
        <input type="hidden" name="txtLinea2" id="txtLinea2" value="<?= urldecode($_GET["linea2"]) ?>">
        <input type="hidden" name="txtLinea3" id="txtLinea3" value="<?= urldecode($_GET["linea3"]) ?>">
        <input type="hidden" name="txtLinea4" id="txtLinea4" value="<?= urldecode($_GET["linea4"]) ?>">
        <input type="hidden" name="txtLinea5" id="txtLinea5" value="<?= urldecode($_GET["linea5"]) ?>">
        <input type="hidden" name="slcCiudad" id="slcCiudad" value="<?= $_GET["idciudad"] ?>">
        <input type="hidden" name="slcTienda" id="slcTienda" value="<?= $_GET["idtienda"] ?>">
        <input type="hidden" name="txtPuesto" id="txtPuesto" value="<?= urldecode($_GET["puesto"]) ?>">
        <input type="hidden" name="permitir_coincidencia" value="1">

        <?php
        if($coincidencias["respuesta"]=="OK"){
        ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Ciudad</th>
                    <th>Tienda</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?
                foreach($coincidencias["coincidencias"] as $tmp){
                ?>
                <tr>
                    <td><?= $tmp["nombre"] ?></td>
                    <td><?= $tmp["telefono"] ?></td>
                    <td><?= $tmp["correo"] ?></td>
                    <td><?= $tmp["ciudad"] ?></td>
                    <td><?= $tmp["tienda"] ?></td>
                    <td class="text-right">
                        <a href="javascript:;" data-fancybox="" data-type="ajax" data-src="/modulos/clientes/detalle.php?idcliente=<?= $tmp["idcliente"] ?>&archivo=0" data-toggle="tooltip" title="Detalle" class="btn btn-success btn-sm">
                            <i class="uil uil-eye"></i>
                        </a>
                        <a href="javascript:;" onclick="seleccionarCliente(<?= $tmp['idcliente'] ?>);" data-toggle="tooltip" title="Seleccionar" class="btn btn-success btn-sm">
                            <i class="uil uil-check"></i>
                        </a>
                    </td>
                </tr>
                <?
                }
                ?>
            </tbody>
        </table>
        <?
        }
        ?>
        
        <button type="button" onclick="validarFormulario('formAgregar');" class="btn btn-primary">Guardar de todos modos</button>
    </form>
</div>
<script>
function seleccionarCliente(idcliente){
    $("#idcliente","#formAgregar").val(idcliente);
    validarFormulario('formAgregar');
}
</script>