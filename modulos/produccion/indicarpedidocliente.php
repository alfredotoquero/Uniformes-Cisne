<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/con.php");
?>

<div class="container" style="width: 30vw;">

    <div class="box-header">
        <center><b>Selecciona un pedido</b> </center>
    </div>

    <form name="formPedidoCliente" id="formPedidoCliente" action="" method="post">
        <input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["idcliente"]; ?>">

        <select name="idpedido" id="idpedido" class="form-control" style="margin-top:60px; margin-bottom:60px">
            <option value="0">--Selecciona un pedido--</option>
            <?
            $query = "select * from vpedidos where idcliente='" . $_GET["idcliente"] . "' and cliente='" . $_GET["cliente"] . "' and statusproduccion!='T' and idpedido in (select idpedido from tespecificaciones where statusproduccion!=2 and pendiente=1)";
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebp.txt", $query);
            $pedidos = mysqli_query($con, $query);

            while ($pedido = mysqli_fetch_assoc($pedidos)) {
            ?>
                <option value="<?= $pedido["idpedido"]; ?>"><?= $pedido["idpedido"]; ?></option>
            <?
            }
            ?>
        </select>

        <div class="row">
            <div class="col-4 offset-5">
                <button type="button" onClick="cargarPDF('formPedidoCliente','/modulos/produccion/','produccionclientepdf',0,'');" class="btn btn-primary btn-sm">GENERAR PDF</button>
            </div>
        </div>
    </form>
</div>