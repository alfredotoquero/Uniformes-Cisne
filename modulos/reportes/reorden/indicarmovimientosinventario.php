<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");

$claseSolicitudes = new Solicitudes();
$claseSucursales = new Sucursales();
$claseAlmacenes = new Almacenes();
$claseProductos = new Productos();
$claseTallas = new Tallas();
$claseColores = new Colores();

$idusuario = $_SESSION["usuario"]["idusuario"];

$almacenes = $claseAlmacenes->obtenerAlmacenes(array());
?>
<script>
    function validarFormIndicarM(){
        $("#btnGuardar").prop("disabled",true);
        var error = false;
        $(".producto").each(function(){
            if(!error){
                var maxgeneral = $(this).data("max");
                var totalproducto = 0;
                $(".almacen",this).each(function(){
                    var maxalmacen = $(this).data("max");
                    if($(this).val()>0){
                        if($(this).val()<=maxalmacen){
                            totalproducto += $(this).val();
                        }else{
                            mensajeerror = "La cantidad solicitada a uno de los almacenes supera el total de existencias para ese almacen.";
                            error = true;
                            return false;
                        }
                    }
                });
                if(!error){
                    if(totalproducto>maxgeneral){
                        mensajeerror = "Las cantidades solicitadas a los almacenes superan el total de productos solicitados.";
                        error = true;
                        return false;
                    }
                }
            }else{
                return false;
            }
        });
        
        if(!error){

            $(".producto").each(function(){
                var idtmp = $(this).data("idtmp");
                $(".almacen",this).each(function(){
                    var idalmacen = $(this).data("idalmacen");
                    if($(this).val()>0){
                        $("#formCotizacion").append("<input type=\"hidden\" name=\"" + idtmp + "-" + idalmacen + "\" value=\"" + $(this).val() + "\">");
                    }
                });
            });

            console.log("entra");
            validarFormulario("formCotizacion");

        }else{
            $("#btnGuardar").prop("disabled",false);
            alert(mensajeerror);
        }
    }
</script>


<div style="width: 900x; height: 600px; padding-left: 20px; padding-right: 20px;">

    <div class="box-header">
        <center><b>Indica la manera en como se solicitarán los productos</b> </center><br>
        <center>(el producto que no se asigne será solicitado)</center>
    </div>

    <?php
    $solicitudes = $claseSolicitudes->obtenerSolicitudesByID($_GET["idsolicitudescompra"]);
    if ($solicitudes["respuesta"]=="OK") {
        foreach($solicitudes["solicitudes"] as $solicitud){
            
            $_POST["idproducto"] = $solicitud["idproducto"];
            $producto = $claseProductos->obtenerProducto($_POST)["producto"];
            $_POST["idcolor"] = $solicitud["idcolor"];
            $color = $claseColores->obtenerColor($_POST)["color"];
            $_POST["idtalla"] = $solicitud["idtalla"];
            $talla = $claseTallas->obtenerTalla($_POST)["talla"];
            echo "<b>".$solicitud["cantidad"]." ".$producto["nombre"]." ".$color["nombre"]." ".$talla["nombre"]."</b>";

            ?>
            <table class="table m-0 producto" data-idsolicitudcompra="<?= $solicitud["idsolicitudcompra"]; ?>" data-max="<?= $solicitud["cantidad"]; ?>">
                <thead>
                    <tr>
                        <?
                        // obtener primero la sucursal seleccionada

                        $almacenes = $claseAlmacenes->obtenerAlmacenes(array(
                            "tipoalmacen" => 1
                        ));
                        foreach($almacenes["almacenes"] as $almacen){
                            if ($almacen["idalmacen"]==$solicitud["idalmacen"]) {
                                continue;
                            }
                            ?>
                            <th align="center"><?= $almacen["nombre"]; ?></th>
                            <?
                        }
                        // obtener todas menos la sucursal seleccionada
                        
                        $sucursales = $claseSucursales->obtenerSucursales(array());
                        foreach($sucursales["sucursales"] as $sucursal){
                            if ($sucursal["idalmacen"]==$solicitud["idalmacen"]) {
                                continue;
                            }
                            ?>
                            <th align="center"><?= $sucursal["nombre"]; ?></th>
                            <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?
                        foreach($almacenes["almacenes"] as $almacen){
                            if ($almacen["idalmacen"]==$solicitud["idalmacen"]) {
                                continue;
                            }
                            $_POST["idalmacen"] = $almacen["idalmacen"];
                            $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                            $cantidadreservada = $datos["reservado"];
                            $existencias = $datos["existencias"];

                            $cantidad = $existencias - $cantidadreservada;
                            ?>
                            <td align="center"><input type="text" placeholder="<?= $cantidad; ?>" class="almacen form-control" data-idalmacen="<?= $almacen["idalmacen"]; ?>" data-max="<?= $cantidad; ?>"></td>
                            <?
                        }
                        foreach($sucursales["sucursales"] as $sucursal){
                            if ($sucursal["idalmacen"]==$solicitud["idalmacen"]) {
                                continue;
                            }
                            $_POST["idalmacen"] = $sucursal["idalmacen"];
                            $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                            $cantidadreservada = $datos["reservado"];
                            $existencias = $datos["existencias"];

                            $cantidad = $existencias - $cantidadreservada;
                            ?>
                            <td align="center"><input type="text" placeholder="<?= $cantidad; ?>" class="almacen form-control" data-idalmacen="<?= $sucursal["idalmacen"]; ?>" data-max="<?= $cantidad; ?>"></td>
                            <?
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            <br>
            <?
        }
    }else{
    ?>
    <script>
        // console.log("entra enviar formcotizacion 1");
        validarFormulario("formCotizacion");
    </script>
    <?
    }
    ?>

    <div class="row">
        <div class="col-12 text-right">
            <button id="btnGuardar" type="button" class="btn btn-primary waves-effect waves-light" onClick="validarFormIndicarMovSol();">Guardar</button>
        </div>
    </div>

</div>