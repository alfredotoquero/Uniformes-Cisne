<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

$claseReportes = new Reportes();
$ventas = $claseReportes->obtenerVentas($_POST);

if($ventas["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th># Ticket</th>
                <th>Tipo</th>
                <th>Detalle</th>
                <th>Vendedor</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($ventas["ventas"] as $ticket){
            ?>
            <tr id="<?= $ticket["idticket"] ?>">
                <td><?= $ticket["folio"] ?></td>
                <td><?= ($ticket["tipocuenta"]=="A" ? "Apartado" : (($ticket["tipocuenta"]=="") ? "Pedido #".$ticket["idpedido"] : "Venta")); ?></td>
                <?
                $detalle = "";
                if ($ticket["productos"]!="") {
                    $cantidades = explode(",",$ticket["cantidades"]);
                    $productos = explode("-_-",$ticket["productos"]);
                    $colores = explode("-_-",$ticket["colores"]);
                    $tallas = explode("-_-",$ticket["tallas"]);
                    foreach ($productos as $i => $producto) {
                        $detalle .= $cantidades[$i] . " " . $producto . " | Talla: " . $tallas[$i] . " Color: " . $colores[$i] . "<br>";
                    }
                }
                ?>
                <td><?= $detalle ?></td>
                <!-- <td></td> -->
                <?
                
                ?>
                <td><?= $ticket["vendedor"] ?></td>
                <?
                $detalle = "$" . number_format($ticket["total"],2);
                if ($ticket["formaspago"]!="") {
                    $formaspago = explode("-",$ticket["formaspago"]);
                    foreach ($formaspago as $formapago) {
                        $datos = explode(":",$formapago);
                        $idformapago = $datos[0];
                        $monto = $datos[1];
    
                        $detalle .= "<br>";
                        
                        $detalle .= (($idformapago==1) ? "Efectivo: $" : "");
                        $detalle .= (($idformapago==2) ? "Efectivo USD: $" : "");
                        $detalle .= (($idformapago==3) ? "Tarjeta: $" : "");
                        $detalle .= (($idformapago==4) ? "Tarjeta de regalo: $" : "");
                        $detalle .= (($idformapago==5) ? "Transferencia: $" : "");
                        $detalle .= (($idformapago==6) ? "Cheque: $" : "");
                        $detalle .= (($idformapago==7) ? "Deposito: $" : "");
    
                        $detalle .= number_format($monto,2);
                    }
                    
                }
                ?>
                <td><?= $detalle; ?></td>
                <td><?= fecha_formateada($ticket["fecha"]); ?></td>
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
// if($tipoimpresion<=2){
                //     $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$ticket["idcuenta"]."'");
                //     while($partida = mysqli_fetch_assoc($partidas)){
                //         echo $partida["cantidad"]." ".$partida["producto"]." | Talla: ".$partida["talla"]." Color: ".$partida["color"]."<br>";
                //     }
                // }else if($tipoimpresion==3){
                //     echo "Abono a apartado";
                // }else{
                //     $partidas = mysqli_query($con,"select * from trcotizacionproductos where idpedido='".$ticket["idpedido"]."' group by idproducto,producto order by idcotizacionproducto");
                //     while($partida = mysqli_fetch_assoc($partidas)){
                //         $producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
                //         if (mysqli_num_rows($producto)>0) {
                //             $nombre = mysqli_fetch_assoc($producto)["nombre"];
                //         } else {
                //             $nombre = $partida["producto"];
                //         }
                //         echo $partida["cantidad"]." ".$nombre."<br>";

                //         $dcolores = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idproducto!=0 group by idcolor");
                //         while($dcolor = mysqli_fetch_assoc($dcolores)){
                        
                //             $color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
                //             $extras = $color;
                        
                //             $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$dcolor["idcolor"]."'");
                //             while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                //                 // talla
                //                 $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                //                 $extras .= " / " . $dtalla["cantidad"] . " - " . $talla;
                //             }

                //             echo " - ".$extras."<br>";
                //         }

                //         $desgloses = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto=0 and producto='".$partida["producto"]."' group by color having color!='' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                //         while ($desglose = mysqli_fetch_assoc($desgloses)) {
                        
                //             $color = $desglose["color"];
                //             $extras = $color; 

                //             $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto=0 and color='".$desglose["color"]."' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                //             while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                //                 // talla
                //                 $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                //                 $extras .= " / " . $dtalla["cantidad"] . " - " . $talla;
                //             }

                //             echo " - ".$extras."<br>";
                //         }
                //     }
                // }
?>