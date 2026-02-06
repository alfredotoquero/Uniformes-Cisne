<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$sucursales = $claseReportes->obtenerCXC($_POST);

if ($sucursales["respuesta"] == "OK") {
    foreach ($sucursales["sucursales"] as $sucursal) {
        if (strlen($sucursal["idpedidos"]) == 0) {
            continue;
        }
        ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Importe</th>
                        <th width="100">Abonado</th>
                        <th width="100">Restante</th>
                        <th width="200">Status Pedido</th>
                        <th width="200">Status Produccion</th>
                    </tr>
                </thead>
                <span class="mb-2"><strong><?= $sucursal["nombre"]; ?></strong></span>
                <tbody>
                    <?

                    $total = 0;
                    $abonado = 0;
                    $restante = 0;

                    $fechas = explode("*,", $sucursal["fechas"]);
                    $idpedidos = explode("*,", $sucursal["idpedidos"]);
                    $clientes = explode("*,", $sucursal["clientes"]);
                    $totales = explode("*,", $sucursal["totales"]);
                    $abonados = explode("*,", $sucursal["abonados"]);
                    $statuspedidos = explode("*,", $sucursal["statuspedidos"]);
                    $statusproduccion = explode("*,", $sucursal["statusproduccion"]);
                    $restantes = array();
                    foreach ($idpedidos as $i => $idpedido) {
                        $restantes[$i] = $totales[$i] - $abonados[$i];
                        if ($restantes[$i] > 0) {
                            ?>
                            <tr>
                                <td><?= fecha_formateada($fechas[$i]); ?></td>
                                <td><?= $idpedido; ?></td>
                                <td><?= $clientes[$i]; ?></td>
                                <td>$<?= number_format($totales[$i], 2); ?></td>
                                <td>$<?= number_format($abonados[$i], 2); ?></td>
                                <td>$<?= number_format($restantes[$i], 2); ?></td>
                                <td><?= (($statuspedidos[$i] == "A") ? "Activo" : (($statuspedidos[$i] == "E") ? "Entregado" : "Cancelado")); ?>
                                </td>
                                <td><?= (($statusproduccion[$i] == "P") ? "En Proceso" : (($statusproduccion[$i] == "T") ? "Terminado" : "No iniciado")); ?>
                                </td>
                            </tr>
                            <?
                            $total += $totales[$i];
                            $abonado += $abonados[$i];
                            $restante += $restantes[$i];
                        }
                    }
                    ?>
                </tbody>
                <tfoot style="font-weight: bold;">
                    <tr>
                        <td>Totales: </td>
                        <td></td>
                        <td></td>
                        <td>$<?= number_format($total, 2); ?></td>
                        <td>$<?= number_format($abonado, 2); ?></td>
                        <td>$<?= number_format(($restante), 2); ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?
    }
?>
<?php
} else {
    ?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $sucursales["mensaje"] ?>
        </div>
    </div>
    <?
}
?>