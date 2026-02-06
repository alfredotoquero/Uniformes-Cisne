<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");


include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$cortes = $claseReportes->obtenerCortes($_POST);

if ($cortes["respuesta"] == "OK") {
?>
    <div class="table-responsive" id="divCortes">
        <table class="table mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Sucursal</th>
                    <th>Vendedor</th>
                    <th>Folio</th>
                    <th>Fecha Inicial</th>
                    <th>Fondo Inicial</th>
                    <th>Ventas</th>
                    <th>Devoluciones</th>
                    <th>Total de Ventas</th>
                    <th>Status</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $totaldevoluciones = 0;
                foreach ($cortes["cortes"] as $corte) {
                    $formaspago = array();
                ?>
                    <tr>
                        <td><?= $corte["sucursal"]; ?></td>
                        <td><?= $corte["vendedor"]; ?></td>
                        <td><?= $corte["folio"]; ?></td>
                        <td><?= fecha_formateada($corte["fechainicial"]); ?></td>
                        <?
                        // si es corte finalizado, se recuperan los valores
                        if ($corte["status"] == "A") {
                            $ventas = $corte["totalventas"];
                            $devoluciones = $corte["totaldevoluciones"];

                            $ticketfinal = $corte["ticketfinal"];
                            $ticketinicial = $corte["ticketinicial"];
                        } else {
                            $ventas = $corte["ventas"];
                            $devoluciones = $corte["devoluciones"];

                            $result = mysqli_query($claseReportes->con, "select a.*, b.total as monto from tcatformaspago a left join tcortesucursal_formaspago b on b.idformapago = a.idformapago and b.idcorte = '" . $corte["idcorte"] . "'");
                            while ($row = mysqli_fetch_array($result)) {
                                $formaspago[] = $row;
                            }

                            $ticketfinal = $corte["ticketfinal"];
                            $ticketinicial = $corte["ticketinicial"];
                        }

                        ?>
                        <td>$<?= number_format($corte["fondoinicial"], 2) ?></td>
                        <td>
                            <?
                            if ($corte["formaspago"] != 0) {
                                $formaspago = explode("-", $corte["formaspago"]);
                                foreach ($formaspago as $formapago) {
                                    $formapago = explode(":", $formapago);
                                    echo $formapago[2] . ": $" . $formapago[1] . "<br>";
                                }
                            }

                            ?>
                        </td>
                        <td style="color: red;"><?= (($devoluciones > 0) ? "$" . number_format($devoluciones, 2) : ""); ?></td>
                        <td>$<?= number_format($ventas, 2); ?></td>
                        <td><?= ($corte["status"] == "A" ? "Activo" : ($corte["status"] == "T" ? "Terminado" : "")); ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    Opciones <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/reportes/cortes/detalleventas.php?idcorte=<?= $corte["idcorte"] ?>">Detalle de ventas</a></li>
                                    <li><a class="dropdown-item" href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/reportes/cortes/detalledevoluciones.php?idcorte=<?= $corte["idcorte"] ?>">Detalle de devoluciones</a></li>
                                    <li class="divider"></li>
                                    <li><a class="dropdown-item" href="/modulos/reportes/cortes/cortegeneral.php?idcorte=<?= $corte["idcorte"]; ?>" target="_blank">Impresión del corte</a></li>
                                    <li><a class="dropdown-item" href="/modulos/reportes/cortes/detallecorte.php?idcorte=<?= $corte["idcorte"]; ?>" target="_blank">Detalle del corte</a></li>
                                    <?
                                    if ($_SESSION["usuario"]["permisoadministrador"] == 1 && $corte["status"] == "T") {
                                        $datos = array("idcorte" => $corte["idcorte"]);
                                        $datos = json_encode($datos);
                                    ?>
                                        <hr>
                                        <li><a class="dropdown-item" href="javascript:;" onclick='solicitarPassword("corregirarqueos","fancy",<?= $datos ?>,"","/modulos/reportes/cortes/corregircorte.php");' title="Activar">Corregir arqueos</a></li>
                                    <?
                                    }
                                    ?>
                                </ul>
                            </div>
                        </td>

                    </tr>
                <?
                    $total += $ventas;
                    $totaldevoluciones += $devoluciones;
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6"></th>
                    <th style="color: red;">$<?= number_format($totaldevoluciones, 2); ?></th>
                    <th>$<?= number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
<?php
} else {
?>
    <div class="card text-white bg-danger">
        <div class="card-body p-3">
            <?= $cortes["mensaje"] ?>
        </div>
    </div>
<?
}
?>