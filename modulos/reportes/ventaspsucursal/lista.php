<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$ventas = $claseReportes->obtenerVentasPorSucursal($_POST);

if($ventas["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Sucursal</th>
                <th>Ventas</th>
                <th>Devoluciones</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($ventas["ventas"] as $venta){
                $total = $venta["totalventas"] - $venta["totaldevoluciones"];
            ?>
            <tr>
                <td><?= $venta["nombre"] ?></td>
                <?
                $detalle = "";
                if ($venta["formaspago"]!="") {
                    $formaspago = explode("-",$venta["formaspago"]);
                    foreach ($formaspago as $formapago) {
                        $datos = explode(":",$formapago);
                        $idformapago = $datos[0];
                        $monto = $datos[1];

                        $detalle .= (($idformapago==1) ? "Efectivo: $" : "");
                        $detalle .= (($idformapago==2) ? "Efectivo USD: $" : "");
                        $detalle .= (($idformapago==3) ? "Tarjeta: $" : "");
                        $detalle .= (($idformapago==4) ? "Tarjeta de regalo: $" : "");
                        $detalle .= (($idformapago==5) ? "Transferencia: $" : "");
                        $detalle .= (($idformapago==6) ? "Cheque: $" : "");
                        $detalle .= (($idformapago==7) ? "Deposito: $" : "");

                        $detalle .= number_format($monto,2);

                        $detalle .= "<br>";
                    }
                }
                $detalle .= "Total de ventas: $" . number_format($venta["totalventas"],2);
                ?>
                <td><?= $detalle ?></td>
                <td>$<?= number_format($venta["totaldevoluciones"],2) ?></td>
                <td>$<?= number_format($total,2); ?></td>
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
        <?= $ventas["mensaje"] ?>
    </div>
</div>
<?
}
?>