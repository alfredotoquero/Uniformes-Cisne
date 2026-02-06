<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TarjetasRegalo.php");

$claseTarjetasRegalo = new TarjetasRegalo();
$tarjetasregalo = $claseTarjetasRegalo->obtenerTarjetasRegalo($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

if($tarjetasregalo["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <form id="formTarjetas" name="formTarjetas">
        <input type="hidden" name="controlador" id="controlador" value="tarjetasregalo">
        <input type="hidden" name="accion" id="accion" value="">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Codigo</th>
                <?
                if ($_POST["activas"]==0) {
                    ?>
                    <th>Último uso</th>
                    <th style="width: 120px;"><input type="checkbox" name="chkTarjetasTodas" id="chkTarjetaTodas" onclick="cambiarChecks(this,'chkTarjeta');"></th>
                    <?
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($tarjetasregalo["tarjetasregalo"] as $tarjetaregalo){
                ?>
                <tr>
                    <td><?= $tarjetaregalo["codigo"] ?></td>
                    <?
                    if ($_POST["activas"]==0) {
                        ?>
                        <td><?= ((!is_null($tarjetaregalo["fecha_ultimo_uso"]) && $tarjetaregalo["fecha_ultimo_uso"]!="0000-00-00 00:00:00") ? fecha_formateada_largo($tarjetaregalo["fecha_ultimo_uso"]) : "-") ?></td>
                        <td>
                            <input type="checkbox" class="chkTarjeta" name="chkTarjetas[]" id="chkTarjeta<?= $tarjetaregalo["codigo"]; ?>" value="<?= $tarjetaregalo["codigo"]; ?>">
                        </td>
                        <?
                    }
                    ?>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
    </form>
</div>
<?php
}else{
?>
<div class="card text-white bg-danger">
    <div class="card-body p-3">
        <?= $tarjetasregalo["mensaje"] ?>
    </div>
</div>
<?
}
?>