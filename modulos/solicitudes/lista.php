<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");

$claseSolicitudes = new Solicitudes();
$solicitudes = $claseSolicitudes->obtenerSolicitudes($_POST);

$mis_solicitudes = explode(",",$_POST["solicitudes"][0]);

if($solicitudes["respuesta"]=="OK"){
?>
<input type="hidden" name="idproveedor" id="idproveedor" value="<?= $_POST["slcProveedor"]; ?>">
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th style="width: 60px;">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" role="switch" onchange="cambiarChecks(this,'chkSolicitud')">
                        <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                    </div>
                </th>
                <th>Producto</th>
                <th>Pedido</th>
                <th>Usuario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($solicitudes["solicitudes"] as $solicitud){
                $cantidad = $solicitud["cantidad"];

                $datosproducto = $cantidad . " " . $solicitud["producto"] . " | Talla: " . $solicitud["talla"] . ", Color: " . $solicitud["color"];

                $detalle = $datosproducto . (($cantidad>1) ? " <small><a href=\"javascript:;\" data-fancybox data-type=\"ajax\" data-src=\"/modulos/solicitudes/dividircantidad.php?idproveedor=" . $_POST["slcProveedor"] . "&idsolicitudcompra=" . $solicitud["idsolicitudcompra"] . "&cantidad=" . $cantidad . "&nombre=" . urlencode($datosproducto) . "\">[Dividir]</a></small><br>" : "") . (($solicitud["notas"]!="") ? "<br></small>".$solicitud["notas"]."</small>" : "");
                ?>
                <tr>
                    <td>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input chkSolicitud" role="switch" name="chkSolicitudes[]" id="chkSolicitud<?= $solicitud["idsolicitudcompra"] ?>" value="<?= $solicitud["idsolicitudcompra"] ?>" <? if(in_array($solicitud["idsolicitudcompra"],$mis_solicitudes)){?> checked <?} ?> onchange="guardarValor(this.value);">
                            <label class="form-check-label"><span class="font-weight-bold"><strong></strong></span></label>
                        </div>
                    </td>
                    <td><?= $detalle ?></td>
                    <td><?= $solicitud["idpedido"] ?></td>
                    <td><?= $solicitud["usuario"] ?></td>
                    <td><?= fecha_formateada($solicitud["fecha"]) ?></td>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $solicitudes["mensaje"] ?>
    </div>
</div>
<?
}
?>