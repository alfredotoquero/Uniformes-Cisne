<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");

$claseProduccion = new Produccion();

$productos = $claseProduccion->getProductosProduccionEspecificacion($_POST["idespecificacion"]);
?>

<table class="table table-striped b-t">
    <thead>
        <tr>
            <th width="40"><? if($productos["produccion"]==1){?><input type="checkbox" onclick="cambiarChecks(this,'chkDesglose')"><?} ?></th>
            <th>PRODUCTO</th>
            <th width="180"></th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach($productos["productos"] as $producto){
        ?>
        <tr>
            <td>
                <? if($producto["habilitado"]==1){ ?>
                <input type="checkbox" class="chkDesglose" name="chkDesglose[]" value="<?= $producto["idespecificacionproducto"]; ?>">
                <? } ?>
            </td>
            <td><?= $producto["nombre"]; ?></td>
            <td id="especificacionproducto<?= $producto["idespecificacionproducto"] ?>">
                <? if($producto["status"]==1){ ?>
                <button type="button" class="btn btn-primary waves-light waves-effect" onClick="solicitudServidor('produccion','terminarproduccion','idespecificacionproducto=<?= $producto["idespecificacionproducto"]; ?>&idespecificacion=<?= $_POST["idespecificacion"] ?>&idcliente=<?= $_POST["idcliente"]; ?>&idpedido<?= $_POST["idpedido"] ?>','','','/produccion');">Terminar</button>
                <? } ?>
            </td>
        </tr>
        <?
        }
        ?>
    </tbody>
</table>