<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");

$claseProduccion = new Produccion();
$claseUsuarios = new Usuarios();
$clasePedidos = new Pedidos();

$especificaciones = $claseProduccion->obtenerEspecificaciones($_POST,$_SESSION);
$usuario = $claseUsuarios->obtenerUsuario(array("idusuario"=>$_SESSION["usuario"]["idusuario"]))["usuario"];

if($especificaciones["respuesta"]=="OK"){
    foreach($especificaciones["especificaciones"] as $especificacion){

        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba2.txt", print_r($especificacion,true));
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white mb-2">
                    <div class="row">
                        <div class="col-4">Fecha de Entrega: <b><?= fecha_formateada($especificacion["fechaentrega"]) ?></b></div>
                        <div class="col-4">Pedido: <b><?= $especificacion["idpedido"] ?></b></div>
                        <div class="col-4 text-end">Usuario: <b><?= $especificacion["usuario"] ?></b></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <?= $especificacion["nombrediseno"] .
                            ($especificacion["serigrafia"]==1 ? "<br> - Serigrafia" : "") .  
                            ($especificacion["digital"]==1 ? "<br> - Digital" : "") . 
                            ($especificacion["bordado"]==1 ? "<br> - Bordado" : "") .  
                            ($especificacion["especificaciones"]!="" ? "<br> - Especificaciones: " . $especificacion["especificaciones"] : "")
                            ?>
                        </div>
                        <div class="col-4">
                            <?
                            foreach($especificacion["desgloses"] as $desglose){
                                echo " ".$desglose["cantidad"]." ".$desglose["producto"].(($desglose["talla"]!="") ? " | Talla: ".$desglose["talla"] : "").(($desglose["color"]!="") ? (($desglose["talla"]!="") ? ", " : " | ")."Color: ".$desglose["color"] : "")."<br>";
                            }
                            ?>
                        </div>
                        <div class="col-4">
                            <div class="row">
                                <div class="col-4 text-center ms-auto">
                                    <i class="fas fa-<?= $especificacion["icono_almacen"] ?>-circle text-<?= $especificacion["status_almacen"] ?>"></i><br>
                                    <small>Almacén</small>
                                    <?
                                    // qué tanto es posible cubrir las especificaciones con las existencias actuales de los productos solicitados en las mismas
                                    if ($especificacion["status_almacen"]!="success") {
                                        // echo "<b>"."<br>".number_format($especificacion["porcentaje_almacen"],2)."%"."</b>"; 
                                        // SE DEJO DE USAR PORCENTAJE, SE USARA LA CANTIDAD FALTANTE, EL PARAMETRO SE MANTENDRA POR SI SE REQUIERE EN UN FUTURO.
                                        echo "<b>"."<br>".$especificacion["cantidadsurtida"]." de ".$especificacion["cantidadrequerida"]." productos asignados"."</b>";
                                    }
                                    ?>
                                </div>
                                <?
                                if ($especificacion["rproduccion"]>0) {
                                    ?>
                                    <div class="col-4 text-center">
                                        <i class="fas fa-<?= $especificacion["icono_produccion"] ?>-circle text-<?= $especificacion["status_produccion"] ?>"></i><br>
                                        <small>Producción</small>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 text-end">
                                    Status actual: 
                                    <b>
                                    <?
                                        if ($especificacion["rproduccion"]>0) {
                                            if ($especificacion["status_produccion"]=="warning") {
                                                ?>
                                                Producción iniciada
                                                <?
                                            }else if ($especificacion["status_produccion"]=="success"){
                                                ?>
                                                Producción finalizada
                                                <?
                                            }
                                        }else{
                                            ?>
                                            -
                                            <?
                                        }
                                        ?>
                                    </b>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <?

                                if (isset($_POST["idcliente"]) and $_POST["idcliente"]>0) {
                                    $url = "idcliente=" . $_POST["idcliente"];
                                } else {
                                    $url = "idpedido=" . $_POST["idpedido"];
                                }
                                

                                if($usuario["permisos_produccion"]){
                                    ?>
                                    <div class="col-12 text-end">
                                    <?
                                    // Usuarios:
                                    // Produccion:
                                    if ($usuario["permiso_produccion"]){
                                        if ($especificacion["status_produccion"]!="success" && $especificacion["statusalmacen"]==1 && $especificacion["rproduccion"]>0) {
                                            ?>
                                            <a href="javascript:;" class="btn btn-sm btn-outline-primary" onclick="fancy('/modulos/produccion/asignarproduccion.php?idespecificacion=<?= $especificacion['idespecificacion'] ?>&idpedido=<?= $especificacion['idpedido'] ?>&idcliente=<?= $_POST['idcliente'] ?>',800,500);" style="float:right;margin-right:5px;">Producción</a>
                                            <?
                                        }
                                    }
                                    // Diseñador:
                                    if ($usuario["permiso_diseno"] && $especificacion["statusdiseno"]==1) {
                                        ?>
                                        <a href="javascript:;" onClick="fancy('/modulos/produccion/enviardiseno.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>',800,500);" class="btn btn-sm btn-outline-primary" style="margin-right:5px;">Enviar diseño</a>
                                        <?
                                    }
                                    // Almacenista:
                                    if ($usuario["permiso_almacen"]){
                                        if ($especificacion["statusalmacen"]==0) {
                                            if ($especificacion["asignar_productos"]) {
                                                ?>
                                                <a href="javascript:;" onClick="fancy('/modulos/produccion/asignarproductos.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>&idpedido=<?=$especificacion["idpedido"]?>',800,500);" class="btn btn-sm btn-outline-primary" style="margin-right:5px;">Asignar Productos</a>
                                                <?
                                            }
                                        }
                                        if ($especificacion["statusdiseno"]==2) {
                                            ?>
                                            <a href="javascript:;" onClick="fancy('/modulos/produccion/revisardiseno.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>&idsolicituddiseno=<?= $especificacion["idsolicituddiseno"] ?>',800,500);" class="btn btn-sm btn-outline-primary" style="margin-right:5px;">Revisar diseño</a>
                                            <?                                    
                                        }
                                    }
                                    ?>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <!-- imagenes -->
                    <?
                    if ((file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen1"]) and $especificacion["imagen1"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen2"]) and $especificacion["imagen2"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen3"]) and $especificacion["imagen3"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen4"]) and $especificacion["imagen4"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen5"]) and $especificacion["imagen5"] != "")) {
                        ?>
                        <div class="row">
                            <div class="col-6">
                            <label for=""><b>Muestras: </b></label>
                                <div class="row">
                                    <?
                                    if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen1"]) and $especificacion["imagen1"] != "") {
                                        ?>
                                        <div class="col-2">
                                            <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen1"] ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"] ?>"><img src="http<?= (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"] ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen1"] ?>" alt="" width="100%" height="50px"></a>
                                        </div>
                                        <?
                                    }
                                    ?>
                                    <?
                                    if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen2"]) and $especificacion["imagen2"] != "") {
                                        ?>
                                        <div class="col-2">
                                            <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen2"] ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"] ?>"><img src="http<?= (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"] ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen2"] ?>" alt="" width="100%" height="50px"></a>
                                        </div>
                                        <?
                                    }
                                    ?>
                                    <?
                                    if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen3"]) and $especificacion["imagen3"] != "") {
                                        ?>
                                        <div class="col-2">
                                            <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen3"] ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"] ?>"><img src="http<?= (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"] ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen3"] ?>" alt="" width="100%" height="50px"></a>
    
                                        </div>
                                        <?
                                    }
                                    ?>
                                    <?
                                    if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen4"]) and $especificacion["imagen4"] != "") {
                                        ?>
                                        <div class="col-2">
                                            <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen4"] ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"] ?>"><img src="http<?= (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"] ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen4"] ?>" alt="" width="100%" height="50px"></a>
                                        </div>
                                        <?
                                    }
                                    ?>
                                    <?
                                    if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen5"]) and $especificacion["imagen5"] != "") {
                                        ?>
                                        <div class="col-2">
                                            <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen5"] ?>" data-fancybox="gallery<?= $especificacion["idespecificacion"] ?>"><img src="http<?= (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"] ?>/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/<?= $especificacion["imagen5"] ?>" alt="" width="100%" height="50px"></a>
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
                    <!-- fin imagenes -->
                    <!-- archivos -->
                    <?
                    if ((file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo1"]) and $especificacion["archivo1"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo2"]) and $especificacion["archivo2"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo3"]) and $especificacion["archivo3"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo4"]) and $especificacion["archivo4"] != "") or
                    (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo5"]) and $especificacion["archivo5"] != "")) {
                        ?>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-6">
                                <label for=""><b>Archivos: </b></label>
                                <br>
                                <?
                                if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo1"]) and $especificacion["archivo1"] != "") {
                                    ?>
                                    
                                        <a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/archivos/<?= $especificacion["archivo1"] ?>" target="_blank"><?= $especificacion["archivo1"] ?></a>
                                    <?
                                }
                                if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo2"]) and $especificacion["archivo2"] != "") {
                                    ?>
                                    
                                        |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/archivos/<?= $especificacion["archivo2"] ?>" target="_blank"><?= $especificacion["archivo2"] ?></a>
                                    <?
                                }
                                if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo3"]) and $especificacion["archivo3"] != "") {
                                    ?>
                                    
                                        |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/archivos/<?= $especificacion["archivo3"] ?>" target="_blank"><?= $especificacion["archivo3"] ?></a>
                                    <?
                                }
                                if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo4"]) and $especificacion["archivo4"] != "") {
                                    ?>
                                    
                                        |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/archivos/<?= $especificacion["archivo4"] ?>" target="_blank"><?= $especificacion["archivo4"] ?></a>
                                    <?
                                }
                                if (file_exists("imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo5"]) and $especificacion["archivo5"] != "") {
                                    ?>
                                        |&nbsp;<a href="/imagenes/especificaciones/<?= $especificacion["idespecificacion"] ?>/archivos/<?= $especificacion["archivo5"] ?>" target="_blank"><?= $especificacion["archivo5"] ?></a>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                    <!-- fin archivos -->
                    <!-- INICIO COMPRAS/MOVIMIENTOS DE LA ESPECIFICACION -->
                    <div class="row">
                        <?
                        $_POST["idpedido"] = $especificacion["idpedido"];
                        $_POST["idespecificacion"] = $especificacion["idespecificacion"];
                        $solicitudes = $claseProduccion->obtenerComprasEspecificacion($_POST);
                        $movimientos = $claseProduccion->obtenerMovimientosEspecificacion($_POST);
                        $asignados = $clasePedidos->obtenerEspecificacionesPedidoSinValidacion($_POST);

                        if ($solicitudes["respuesta"]=="OK") {
                            ?>
                            <div class="col-1 mb-2">
                                <a href="javascript:;" class="btn btn-sm btn-info waves-effect waves-light" onClick="mostrarInfoDiv('<?= $especificacion["idespecificacion"] ?>','divCompras');">Compras</a>
                            </div>&nbsp;
                            <?
                        }
                        if ($movimientos["respuesta"]=="OK") {
                            ?>
                            <div class="col-1 mb-2">
                                <a href="javascript:;" class="btn btn-sm btn-info waves-effect waves-light" onClick="mostrarInfoDiv('<?= $especificacion["idespecificacion"] ?>','divMovimientos');">Movimientos</a>
                            </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?
                        }
                        if ($asignados["respuesta"] == "OK"){
                            ?>
                            <div class="col-1 mb-2">
                                <a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/pedidos/especificacionde.php?idespecificacion=<?= $especificacion["idespecificacion"] ?>&idcliente=<?= $_GET["idcliente"]; ?>&idpedido=<?= $especificacion["idpedido"]; ?>" class="btn btn-sm btn-info waves-effect waves-light" title="Asignaciones">Asignaciones</a>
                            </div>
                            <?
                        }
                        ?>

                    </div>
                    <div class="row" id="divCompras<?= $especificacion["idespecificacion"] ?>" style="display:none;">
                        <div class="col-12">
                            <?
                            if ($solicitudes["respuesta"]=="OK") {
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
                                        foreach($solicitudes["productos"] as $producto){
                                            if($producto["idcompraproducto"]>0){
                                                if($producto["cantidad_recibida"]==0){
                                                    $status = "Sin Recibir";
                                                }else{
                                                    $status = "Recibido";
                                                }
                                            }else{
                                                $status = "Sin Comprar";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= ($producto["idcompra"]==0 ? "-" : $producto["idcompra"]) ?></td>
                                                <td><?= $producto["producto"] ?></td>
                                                <td><?= $producto["talla"] ?></td>
                                                <td><?= $producto["color"] ?></td>
                                                <td><?= $producto["cantidad"] ?></td>
                                                <td><?= $status ?></td>
                                            </tr>
                                            <?
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?
                            }
                            ?>            
                        </div>
                    </div>
                    <div class="row mt-2" id="divMovimientos<?= $especificacion["idespecificacion"] ?>" style="display:none;">
                        <div class="col-12">
                            <?
                            if($movimientos["respuesta"]=="OK"){
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
                                        foreach($movimientos["productos"] as $producto){
                                            if($producto["autorizacion"]==0){
                                                $status = "Sin Autorizar";
                                            }else if($producto["autorizacion"]==2){
                                                if($producto["recepcionparcial"]==0){
                                                    $status = "Autorizado";
                                                }else if($producto["recepcionparcial"]==1){
                                                    $status = "Recibido Parcialmente";
                                                }else if($producto["recepcionparcial"]==2){
                                                    $status = "Recibido";
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td><?= ($producto["idmovimientoinventario"]==0 ? "-" : $producto["idmovimientoinventario"]) ?></td>
                                                <td><?= $producto["producto"] ?></td>
                                                <td><?= $producto["talla"] ?></td>
                                                <td><?= $producto["color"] ?></td>
                                                <td><?= $producto["cantidad"] ?></td>
                                                <td><?= $status ?></td>
                                                <td><?= ($producto["cantidadrecibida"]==0 ? "-" : $producto["cantidadrecibida"]) ?></td>
                                            </tr>
                                            <?
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                    <!-- FIN COMPRAS/MOVIMIENTOS DE LA ESPECIFICACION -->
                </div>
            </div>
        </div>
    </div>
    <?
    }
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $especificaciones["mensaje"] ?>
    </div>
</div>
<?
}
?>