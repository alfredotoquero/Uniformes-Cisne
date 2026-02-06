<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cuentas.php");

$claseCuentas = new Cuentas();
$cuentas = $claseCuentas->obtenerCuentas($_POST);

if($cuentas["respuesta"]=="OK"){
?>
<div class="table-responsive">
    
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th># Ticket</th>
                <th>Vendedor</th>
                <th>Sucursal</th>
                <th>Total</th>
                <th>Fecha</th>
                <th style="width:50px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($cuentas["cuentas"] as $cuenta){
            ?>
            <tr>
                <td><?= $cuenta["idcuenta"] ?></td>
                <td><?= "Vendedor" ?></td>
                <td><?= "Sucursal" ?></td>
                <td>$<?= number_format($cuenta["total"],2) ?></td>
                <td><?= fecha_formateada($cuenta["fecha"],2) ?></td>
                <td>
                    <a href="javascript:;" onclick="solicitudServidor('cuentas','entregar','idcuenta=<?= $cuenta['idcuenta'] ?>','¿Deseas marcar la cuenta como entregada?','');" data-toggle="tooltip" title="entregar" class="btn btn-success btn-sm">Entregar</a>
                </td>
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
        <?= $cuentas["mensaje"] ?>
    </div>
</div>
<?
}
?>