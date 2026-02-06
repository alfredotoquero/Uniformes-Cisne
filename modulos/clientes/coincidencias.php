<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$coincidencias = $claseClientes->buscarCoincidenciasClientes(array(
    "nombre" => urldecode($_GET["nombre"]),
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
        <input type="hidden" name="controlador" id="controlador" value="clientes">
        <input type="hidden" name="accion" id="accion" value="agregar">
        <input type="hidden" name="txtNombre" value="<?= $_GET["nombre"] ?>">
        <input type="hidden" name="txtTelefono" value="<?= $_GET["telefono"] ?>">
        <input type="hidden" name="txtCorreo" value="<?= $_GET["correo"] ?>">
        <input type="hidden" name="slcCiudad" value="<?= $_GET["idciudad"] ?>">
        <input type="hidden" name="slcTienda" value="<?= $_GET["idtienda"] ?>">
        <input type="hidden" name="permitir_coincidencia" value="1">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">

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
                        <a href="javascript:;" data-fancybox="" data-type="ajax" data-src="/modulos/clientes/detalle.php?idcliente=<?= $tmp["idcliente"] ?>&archivo=0" data-toggle="tooltip" title="detalle" class="btn btn-success btn-sm">
                            <i class="uil uil-eye"></i>
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
        <button type="button" onclick="$.fancybox.close()" class="btn btn-secondary">Cancelar</button>
    </form>
</div>