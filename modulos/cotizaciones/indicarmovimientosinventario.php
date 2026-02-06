<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Almacenes.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tallas.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Colores.php");

$claseCotizaciones = new Cotizaciones();
$claseSucursales = new Sucursales();
$claseAlmacenes = new Almacenes();
$claseProductos = new Productos();
$claseTallas = new Tallas();
$claseColores = new Colores();

$idusuario = $_SESSION["usuario"]["idusuario"];
$_POST = $_GET;
$misucursal = $claseSucursales->obtenerSucursal($_POST)["sucursal"];
$idalmacensucursal = $misucursal["idalmacen"];
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
        <center>(el producto que no se asigne será solicitado como orden de compra)</center>
    </div>

    <?php
    $desgloses = $claseCotizaciones->obtenerDesglosesInventarioTMP($idusuario);
    if ($desgloses["respuesta"]=="OK") {
        foreach($desgloses["desgloses"] as $desglose){
            
            $_POST["idproducto"] = $desglose["idproducto"];
            $producto = $claseProductos->obtenerProducto($_POST)["producto"];
            $_POST["idcolor"] = $desglose["idcolor"];
            $color = $claseColores->obtenerColor($_POST)["color"];
            $_POST["idtalla"] = $desglose["idtalla"];
            $talla = $claseTallas->obtenerTalla($_POST)["talla"];
            echo "<b>".$desglose["cantidad"]." ".$producto["nombre"]." ".$color["nombre"]." ".$talla["nombre"]."</b>";

            ?>
            <table class="table m-0 producto" data-idtmp="<?= $desglose["idtmp"]; ?>" data-max="<?= $desglose["cantidad"]; ?>">
                <thead>
                    <tr>
                        <th align="center"><?= $misucursal["nombre"]; ?></th>
                        <?
                        // obtener primero la sucursal seleccionada

                        $_POST["tipoalmacen"] = 1;
                        $almacenes = $claseAlmacenes->obtenerAlmacenes($_POST);
                        foreach($almacenes["almacenes"] as $almacen){
                            if ($almacen["idalmacen"]==$idalmacensucursal) {
                                continue;
                            }
                            ?>
                            <th align="center"><?= $almacen["nombre"]; ?></th>
                            <?
                        }
                        // obtener todas menos la sucursal seleccionada
                        
                        $sucursales = $claseSucursales->obtenerSucursales($_POST);
                        foreach($sucursales["sucursales"] as $sucursal){
                            if ($sucursal["idalmacen"]==$idalmacensucursal) {
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
                        $_POST["idalmacen"] = $misucursal["idalmacen"];
                        $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                        $cantidadreservada = $datos["reservado"];
                        $existencias = $datos["existencias"];

                        $cantidad = $existencias - $cantidadreservada;
                        ?>
                        <td align="center"><input type="text" placeholder="<?= $cantidad; ?>" class="almacen form-control" data-idalmacen="<?= $misucursal["idalmacen"]; ?>" data-max="<?= $cantidad; ?>"></td>
                        <?
                        foreach($almacenes["almacenes"] as $almacen){
                            if ($almacen["idalmacen"]==$idalmacensucursal) {
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
                            if ($sucursal["idalmacen"]==$idalmacensucursal) {
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
            <button id="btnGuardar" type="button" class="btn btn-primary waves-effect waves-light" onClick="validarFormIndicarM();">Guardar</button>
        </div>
    </div>

</div>