<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");

$clasePedidos = new Pedidos();

$_POST["idpedido"] = $_GET["idpedido"];
$_POST["tipo"] = 1;

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>
<script>
    function entregar() {
        if ($('.chkDesglose:checked').length > 0 || $('.chkProducto:checked').length > 0) {
            // $("#formEntrega").submit();
            validarFormulario("formEntrega");
        } else {
            alert("ATENCION: Debes indicar al menos una partida para entregar.");
        }
    }
</script>
<div style="width:800px; height: 500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Entregar Pedido</h4><br>
            <small>Indica las partidas que se entregarán.</small>
        </div>
    </div>
    <hr>
    <form id="formEntrega" name="formEntrega">
        <input type="hidden" name="controlador" id="controlador" value="pedidos">
        <input type="hidden" name="accion" id="accion" value="entregar">
        <input type="hidden" name="idpedido" id="idpedido" value="<?= $_GET["idpedido"] ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"]; ?>">
        <table class="table table-striped b-t">
            <thead>
                <tr>
                    <th width="40"></th>
                    <th>PRODUCTO</th>
                </tr>
            </thead>
            <tbody>
                <?
                $requerido = 0;
                $solicitado = 0;
                $desgloses = $clasePedidos->obtenerDesglose($_POST);
                foreach ($desgloses["desgloses"] as $desglose) {
                    if ($desglose["idpedidoproducto"] > 0) {
                        $talla = $desglose["talla"];
                        $color = $desglose["color"];
                        $nombre = $desglose["producto"];
                        ?>
                        <tr>
                            <td>
                                <? if ($desglose["status_entrega"] == 0) { ?>
                                    <input type="checkbox" class="chkDesglose" name="chkDesglose[]"
                                        value="<?= $desglose["idespecificacionproducto"]; ?>">
                                <? } ?>
                            </td>
                            <td><?= $nombre . " | Talla: " . $talla . ", Color: " . $color; ?></td>
                        </tr>
                        <?
                    } else {
                        $nombre = $desglose["producto"];
                        ?>
                        <tr>
                            <td>
                                <?
                                if ($desglose["status_entrega"] == 0) {
                                    ?>
                                    <input type="checkbox" class="chkDesglose" name="chkDesglose[]"
                                        value="<?= $desglose["idespecificacionproducto"]; ?>">
                                    <?
                                }
                                ?>
                            </td>
                            <td><?= $nombre; ?></td>
                        </tr>
                        <?
                    }
                }
                // obtener todas las partidas sin desglose
                $partidas = $clasePedidos->obtenerPartidasSinDesglose($_POST);
                foreach ($partidas["partidas"] as $partida) {
                    ?>
                    <tr>
                        <td>
                            <?
                            if ($partida["status"] == '') {
                                ?>
                                <input type="checkbox" class="form-control chkProducto" name="chkProducto[]"
                                    value="<?= $partida["idpedidoproducto"]; ?>">
                                <?
                            }
                            ?>
                        </td>
                        <td><?= $partida["producto"] . " | Talla: " . $partida["talla"] . ", Color: " . $partida["color"]; ?>
                        </td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
        <button type="button" onclick="entregar();" class="btn btn-primary">Guardar</button>
    </form>
</div>