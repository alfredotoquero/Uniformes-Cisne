<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Clientes.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Movimientos.php");

$clasePedidos = new Pedidos();
$claseClientes = new Clientes();
$claseMovimientos = new Movimientos();

$_POST["idpedido"] = $_GET["idpedido"];
$pedido = $clasePedidos->obtenerPedido($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<div style="width:1200px;">
    <?
    if ($pedido["respuesta"] == "OK") {
        $pedido = $pedido["pedido"];
    ?>
        <div class="card">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Información del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12"><b>Cliente:</b> <?= $pedido["cliente"]; ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">Correo: <?= $pedido["correocliente"]; ?></div>
                    <div class="col-xs-12 col-md-6">Telefono: <?= $pedido["telefonocliente"]; ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-xs-12 col-md-12"><b>Contacto:</b> <?= $pedido["contacto"]; ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">Correo: <?= $pedido["correocontacto"]; ?></div>
                    <div class="col-xs-12 col-md-6">Telefono: <?= $pedido["telefonocontacto"]; ?></div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 col-md-4">
                <div class="border border-light p-3 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="font-18 mb-1">Total</p>
                            <h3 class="text-primary my-0">$<?= number_format($pedido["total"], 2); ?></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle h3 my-0">
                                <i class="uil uil-dollar-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="border border-light p-3 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="font-18 mb-1">Abonado</p>
                            <h3 class="text-primary my-0">$<?= number_format($pedido["abonado"], 2); ?></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle h3 my-0">
                                <i class="uil uil-dollar-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="border border-light p-3 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="font-18 mb-1">Restante</p>
                            <h3 class="text-primary my-0">$<?= number_format($pedido["total"] - $pedido["abonado"], 2); ?></h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle h3 my-0">
                                <i class="uil uil-dollar-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?
        if ($pedido["motivofinalizacion"] != "") {
        ?>
            <div class="card mt-2">
                <div class="card-header bg-primary text-white text-center"><b>
                        <h4>Motivo de finalización</h4>
                    </b></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-12"><b>Fecha:</b> <?= (($pedido["fechaactualizacion"] != "") ? fecha_formateada_largo($pedido["fechaactualizacion"]) : ""); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6"><b>Usuario:</b> <?= $pedido["usuariofinalizacion"]; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12"><b>Status:</b> <?= (($pedido["status"] == "E") ? "Entregado" : "Cancelado"); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6"><b>Comentarios:</b> <?= $pedido["motivofinalizacion"]; ?></div>
                    </div>
                </div>
            </div>
        <?
        }
        ?>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Historial de Pagos del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>

            <div class="card-body">
                <?
                $pagos = $clasePedidos->obtenerPagos($_POST);
                if ($pagos["respuesta"] == "OK") {
                ?>
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Monto</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach ($pagos["pagos"] as $pago) {
                                if ($pago["idusuario"] > 0) {
                                    $nombre = $pago["usuario"];
                                } else {
                                    $nombre = $pago["vendedor"];
                                }
                            ?>
                                <tr>
                                    <td>Ticket #<?= $pago["folio"]; ?></td>
                                    <td><?= "$" . number_format($pago["total"], 2); ?></td>
                                    <td><?= $nombre; ?></td>
                                    <td><?= fecha_formateada($pago["fecha"]); ?></td>
                                </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                <?
                } else {
                ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            No se encontraron pagos registrados
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Productos del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>

            <div class="card-body">
                <?
                $partidas = $clasePedidos->obtenerPartidasCotizacion($_POST);
                if ($partidas["respuesta"] == "OK") {
                ?>
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P.U.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach ($partidas["partidas"] as $partida) {
                                $nombreproducto = $partida["producto"];
                            ?>
                                <tr>
                                    <td>
                                        <?
                                        $detalle = "";

                                        $cadenaespecificaciones = "";
                                        $cadenaespecificaciones .= (($partida["serigrafia1"] > 0 || $partida["serigrafia2"] > 0 || $partida["serigrafia3"] > 0) ? "Serigrafía " . $partida["serigrafia1"] . " / " . $partida["serigrafia2"] . " / " . $partida["serigrafia3"] : "");
                                        $cadenaespecificaciones .= ($partida["personalizadonumero"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado Número" : "");
                                        $cadenaespecificaciones .= ($partida["personalizadonombre"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado Nombre" : "");
                                        $cadenaespecificaciones .= ($partida["bordado1"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "1 Bordado" : "");
                                        $cadenaespecificaciones .= ($partida["bordado2"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "2 Bordados" : "");
                                        $cadenaespecificaciones .= ($partida["bordado3"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "3 Bordados" : "");
                                        $cadenaespecificaciones .= ($partida["bordado4"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "4 Bordados" : "");
                                        $cadenaespecificaciones .= ($partida["bordadoespecial"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Bordado Especial" : "");
                                        $cadenaespecificaciones .= ($partida["personalizado1linea"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 1 Linea" : "");
                                        $cadenaespecificaciones .= ($partida["personalizado2lineas"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 2 Lineas" : "");
                                        $cadenaespecificaciones .= ($partida["personalizado3lineas"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 3 Lineas" : "");
                                        $cadenaespecificaciones .= ($partida["sxl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "S-XL" : "");
                                        $cadenaespecificaciones .= ($partida["2xl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "2XL" : "");
                                        $cadenaespecificaciones .= ($partida["3xl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "3XL" : "");
                                        $cadenaespecificaciones .= ($partida["observaciones"] != "" ? (($cadenaespecificaciones != "") ? ", " : "") . "Observaciones: " . $partida["observaciones"] : "");
                                        $detalle .= $nombreproducto;

                                        $detalle .= ($cadenaespecificaciones != "") ? "<br><br>" . $cadenaespecificaciones . "<br>" : "<br><br>";

                                        $desgloses = explode(";", $partida["desgloses"]);
                                        foreach ($desgloses as $desglose) {
                                            $desglose = explode(" : ", $desglose);
                                            $desglose = array("color" => $desglose[0], "tallas" => $desglose[1]);

                                            $detalle .= "<br>" . $desglose["color"] . " / ";

                                            $detalle .= $desglose["tallas"];
                                            // $tallas = explode(";",$desglose["tallas"]);
                                            // foreach ($tallas as $talla) {
                                            //     $talla = explode(",",$talla);
                                            //     $talla = array("talla"=>$talla[0],"cantidad"=>$talla[1]);

                                            //     $detalle .= " / " . $talla["cantidad"] . " - " . $talla["talla"];
                                            // }
                                        }
                                        $detalle = rtrim($detalle, "/ <br>");

                                        echo $detalle;
                                        ?>
                                    </td>
                                    <td><?= $partida["cantidad"]; ?></td>
                                    <td><?= "$" . number_format($partida["precio"], 2); ?></td>
                                    <td><?= "$" . number_format(($partida["precio"] * $partida["cantidad"]), 2); ?></td>
                                </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                <?
                } else {
                ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            No se encontraron productos registrados para este pedido
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Producción del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>
            <div class="card-body">
                <?
                $iconos = array("stop", "play", "check");
                // $_POST["idpedido"] = $pedido["idpedido"];
                // $_POST["pendiente"] = 1;
                $especificaciones = $clasePedidos->obtenerEspecificaciones($_POST);

                file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebqqq.txt", print_r($especificaciones, true));
                if ($especificaciones["respuesta"] == "OK") {
                    foreach ($especificaciones["especificaciones"] as $especificacion) {
                        //status diseño
                        if ($especificacion["statusdiseno"] > 0) {
                            if ($especificacion["statusdiseno"] < 4) {
                                $iconodiseno = $iconos[1];
                                $statusdiseno = "warning";
                            } else {
                                $iconodiseno = $iconos[2];
                                $statusdiseno = "success";
                            }
                        } else {
                            $iconodiseno = $iconos[0];
                            $statusdiseno = "danger";
                        }

                        //status produccion
                        if ($especificacion["statusproduccion"] > 0) {
                            if ($especificacion["statusproduccion"] < 2) {
                                $iconoproduccion = $iconos[1];
                                $statusproduccion = "warning";
                            } else {
                                $iconoproduccion = $iconos[2];
                                $statusproduccion = "success";
                            }
                        } else {
                            $iconoproduccion = $iconos[0];
                            $statusproduccion = "danger";
                        }

                        //status almacen
                        if ($especificacion["cantidadsurtida"] > 0) {
                            if ($especificacion["cantidadrequerida"] > $especificacion["cantidadsurtida"]) {
                                $iconoalmacen = $iconos[1];
                                $statusalmacen = "warning";
                            } else {
                                $iconoalmacen = $iconos[2];
                                $statusalmacen = "success";
                            }
                        } else if ($especificacion["cantidadrequerida"] > 0) {
                            $iconoalmacen = $iconos[0];
                            $statusalmacen = "danger";
                        } else {
                            $iconoalmacen = $iconos[2];
                            $statusalmacen = "success";
                        }
                ?>
                        <div class="row p-2" style="border-top: 1px solid rgba(222,222,222,0.5);">
                            <div class="col-xs-12 col-md-12">
                                <div class="row mb-2">
                                    <div class="col-6 text-left">
                                        Fecha de Entrega: <?= fecha_formateada($especificacion["fechaentrega"]); ?>
                                    </div>
                                    <div class="col-6 text-end">
                                        Usuario: <?= $especificacion["usuario"]; ?>
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <?= $especificacion["nombrediseno"] .
                                            ($especificacion["serigrafia"] == 1 ? "<br> - Serigrafia" : "") .
                                            ($especificacion["digital"] == 1 ? "<br> - Digital" : "") .
                                            ($especificacion["bordado"] == 1 ? "<br> - Bordado" : "") .
                                            ($especificacion["especificaciones"] != "" ? "<br> - Especificaciones: " . $especificacion["especificaciones"] : "");
                                        ?>
                                    </div>
                                    <div class="col-4">
                                        <?
                                        $detalle = "";
                                        // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/prueba1.txt",print_r($especificacion,true));
                                        // $desgloses = explode(";",$especificacion["desgloses"]);
                                        // foreach($desgloses as $desglose){
                                        //     $datos = explode(",",$desglose);
                                        //     $cantidad = $datos[0];
                                        //     $producto = $datos[1];
                                        //     $talla = $datos[2];
                                        //     $color = $datos[3];
                                        //     $detalle .= " ".$cantidad." ".$producto.(($talla!="") ? " | Talla: ".$talla : "").(($color!="") ? (($talla!="") ? ", " : " | ")."Color: ".$color : "")."<br>";
                                        // }
                                        $detalle = str_replace("\n", "<br>", $especificacion["desgloses"]) . "<br>" . str_replace("\n", "<br>", $especificacion["partidas"]);

                                        echo $detalle;

                                        ?>
                                    </div>
                                    <div class="col-4">
                                        <div class="row">
                                            <div class="col-4 text-center">
                                                <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/especificacionde.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>&idcliente=<?= $_GET["idcliente"]; ?>&idpedido=<?= $especificacion["idpedido"]; ?>" class="btn btn-sm" title="Informacion">
                                                    <i class="fas fa-<?= $iconoalmacen; ?>-circle text-<?= $statusalmacen; ?>"></i><br>
                                                    <small>Almacen</small>
                                                </a>
                                            </div>
                                            <div class="col-4 text-center">
                                                <i class="fas fa-<?= $iconodiseno; ?>-circle text-<?= $statusdiseno; ?>"></i><br>
                                                <small>Diseño</small>
                                            </div>
                                            <div class="col-4 text-center">
                                                <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/asignarproduccionne.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>&idcliente=<?= $_GET["idcliente"]; ?>&idpedido=<?= $especificacion["idpedido"]; ?>" class="btn btn-sm" title="Informacion">
                                                    <i class="fas fa-<?= $iconoproduccion; ?>-circle text-<?= $statusproduccion; ?>"></i><br>
                                                    <small>Producción</small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- imagenes -->
                                <?
                                if ((file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen1"]) and $especificacion["imagen1"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen2"]) and $especificacion["imagen2"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen3"]) and $especificacion["imagen3"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen4"]) and $especificacion["imagen4"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen5"]) and $especificacion["imagen5"] != "")
                                ) {
                                ?>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for=""><b>Muestras: </b></label>
                                            <div class="row">
                                                <?
                                                if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen1"]) and $especificacion["imagen1"] != "") {
                                                ?>
                                                    <div class="col-2">
                                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen1"]; ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"]; ?>"><img src="http<?= (($_SERVER["HTTPS"] != "") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen1"]; ?>" alt="" width="100%" height="50px"></a>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                                <?
                                                if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen2"]) and $especificacion["imagen2"] != "") {
                                                ?>
                                                    <div class="col-2">
                                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen2"]; ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"]; ?>"><img src="http<?= (($_SERVER["HTTPS"] != "") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen2"] ?>" alt="" width="100%" height="50px"></a>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                                <?
                                                if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen3"]) and $especificacion["imagen3"] != "") {
                                                ?>
                                                    <div class="col-2">
                                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen3"]; ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"]; ?>"><img src="http<?= (($_SERVER["HTTPS"] != "") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen3"] ?>" alt="" width="100%" height="50px"></a>

                                                    </div>
                                                <?
                                                }
                                                ?>
                                                <?
                                                if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen4"]) and $especificacion["imagen4"] != "") {
                                                ?>
                                                    <div class="col-2">
                                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen4"]; ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"]; ?>"><img src="http<?= (($_SERVER["HTTPS"] != "") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen4"] ?>" alt="" width="100%" height="50px"></a>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                                <?
                                                if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/" . $especificacion["imagen5"]) and $especificacion["imagen5"] != "") {
                                                ?>
                                                    <div class="col-2">
                                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/<?= $especificacion["imagen5"]; ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"]; ?>"><img src="http<?= (($_SERVER["HTTPS"] != "") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen5"] ?>" alt="" width="100%" height="50px"></a>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                                <!-- archivos -->
                                <?
                                if ((file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo1"]) and $especificacion["archivo1"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo2"]) and $especificacion["archivo2"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo3"]) and $especificacion["archivo3"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo4"]) and $especificacion["archivo4"] != "") or
                                    (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo5"]) and $especificacion["archivo5"] != "")
                                ) {
                                ?>
                                    <div class="row" style="margin-top:10px;">
                                        <div class="col-6">
                                            <label for=""><b>Archivos: </b></label>
                                            <br>
                                            <?
                                            if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo1"]) and $especificacion["archivo1"] != "") {
                                            ?>

                                                <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/archivos/<?= $especificacion["archivo1"]; ?>" target="_blank"><?= $especificacion["archivo1"]; ?></a>
                                            <?
                                            }
                                            if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo2"]) and $especificacion["archivo2"] != "") {
                                            ?>

                                                |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/archivos/<?= $especificacion["archivo2"]; ?>" target="_blank"><?= $especificacion["archivo2"]; ?></a>
                                            <?
                                            }
                                            if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo3"]) and $especificacion["archivo3"] != "") {
                                            ?>

                                                |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/archivos/<?= $especificacion["archivo3"]; ?>" target="_blank"><?= $especificacion["archivo3"]; ?></a>
                                            <?
                                            }
                                            if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo4"]) and $especificacion["archivo4"] != "") {
                                            ?>

                                                |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/archivos/<?= $especificacion["archivo4"]; ?>" target="_blank"><?= $especificacion["archivo4"]; ?></a>
                                            <?
                                            }
                                            if (file_exists("../../imagenes/especificaciones/" . $especificacion["idespecificacion"] . "/archivos/" . $especificacion["archivo5"]) and $especificacion["archivo5"] != "") {
                                            ?>
                                                |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"]; ?>/archivos/<?= $especificacion["archivo5"]; ?>" target="_blank"><?= $especificacion["archivo5"]; ?></a>
                                            <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                                <!-- fin archivos -->
                            </div>
                        </div>
                    <?
                    }
                } else {
                    ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            <?= $especificaciones["mensaje"] ?>
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Productos asignados del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>

            <div class="card-body">
                <?
                $productos = $clasePedidos->obtenerProductosAsignados($_POST);
                if ($productos["respuesta"] == "OK") {
                ?>
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Cantidad</th>
                                <th>Producto</th>
                                <th>Usuario</th>
                                <th>Almacen</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach ($productos["productos"] as $producto) {
                            ?>
                                <tr>
                                    <td><?= $producto["cantidad"]; ?></td>
                                    <td><?= $producto["producto"]; ?></td>
                                    <td><?= $producto["usuario"]; ?></td>
                                    <td><?= $producto["almacen"]; ?></td>
                                    <td><?= $producto["fecha"]; ?></td>
                                </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                <?
                } else {
                ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            No hay productos asignados a este pedido
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Compras del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>

            <div class="card-body">
                <?
                $solicitudes = $clasePedidos->obtenerSolicitudes($_POST);
                if ($solicitudes["respuesta"] == "OK") {
                ?>
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th># Compra</th>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach ($solicitudes["solicitudes"] as $partida) {
                            ?>
                                <tr>
                                    <td><?= ($partida["idcompra"] == 0 ? "-" : $partida["idcompra"]); ?></td>
                                    <td><?= $partida["producto"]; ?></td>
                                    <td><?= $partida["talla"]; ?></td>
                                    <td><?= $partida["color"]; ?></td>
                                    <td><?= $partida["cantidad"]; ?></td>
                                    <td>
                                        <?php
                                        if ($partida["idcompraproducto"] > 0) {
                                            if ($partida["cantidad_recibida"] == 0) {
                                                echo "Sin Recibir";
                                            } else {
                                                echo "Recibido";
                                            }
                                        } else {
                                            echo "Sin Comprar";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                <?
                } else {
                ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            No se realizaron compras para este pedido
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header bg-primary text-white text-center"><b>
                    <h4>Movimientos del Pedido #<?= $pedido["idpedido"]; ?></h4>
                </b></div>

            <div class="card-body">
                <?
                $movimientos = $clasePedidos->obtenerMovimientos($_POST);
                if ($movimientos["respuesta"] == "OK") {
                ?>
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th># Movimiento</th>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Status</th>
                                <th>Cantidad Recibida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            foreach ($movimientos["movimientos"] as $partida) {
                            ?>
                                <tr>
                                    <td><?= ($partida["idmovimientoinventario"] == 0 ? "-" : $partida["idmovimientoinventario"]); ?></td>
                                    <td><?= $partida["producto"]; ?></td>
                                    <td><?= $partida["talla"]; ?></td>
                                    <td><?= $partida["color"]; ?></td>
                                    <td><?= $partida["cantidad"]; ?></td>
                                    <td>
                                        <?
                                        $_POST["idmovimientoinventario"] = $partida["idmovimientoinventario"];
                                        $movimiento = $claseMovimientos->obtenerMovimiento($_POST)["movimiento"];
                                        if ($movimiento["autorizacion"] == 0) {
                                            echo "Sin Autorizar";
                                        } else if ($movimiento["autorizacion"] == 2) {
                                            if ($movimiento["recepcionparcial"] == 0) {
                                                echo "Autorizado";
                                            } else if ($movimiento["recepcionparcial"] == 1) {
                                                echo "Recibido Parcialmente";
                                            } else if ($movimiento["recepcionparcial"] == 2) {
                                                echo "Recibido";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td><?= ($partida["cantidadrecibida"] == 0 ? "-" : $partida["cantidadrecibida"]); ?></td>
                                </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                <?
                } else {
                ?>
                    <div class="card text-white bg-danger mb-0">
                        <div class="card-body p-3">
                            No se realizaron movimientos para este pedido
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    <?
    } else {
    ?>
        <div class="card text-white bg-danger">
            <div class="card-body p-3">
                <?= $pedidos["mensaje"] ?>
            </div>
        </div>
    <?
    }
    ?>

</div>