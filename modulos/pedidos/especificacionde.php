<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

$_POST["idpedido"] = $_GET["idpedido"];
$_POST["idespecificacion"] = $_GET["idespecificacion"];

?>

<div style="width:1100px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Especificación de Almacen</h4>
        </div>
    </div>
    <hr>

    <div class="padding">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-6">
                        <h4>Pedido #<?=$_POST["idpedido"]?></h4>
                    </div>

                    <div class="col offset-sm-4">
                    <a class="btn btn-danger waves-effect pull-right" style="float:right;margin-right:0px;margin-left:10px;">Generar PDF</a>
                    </div>
                </div>
            </div>
    
            <div class="box-body">
                <div id="divListaPedidoEsp"><? include($_SERVER["DOCUMENT_ROOT"]."/modulos/pedidos/especificacionde/lista.php");?></div>
            </div>
        </div>
    </div>
</div>