<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Parametros.php");

$p = new Parametros();
$parametros = $p->getParametros($_POST);

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

if($parametros["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <form id="formParametros" name="formParametros">
        <input type="hidden" name="controlador" id="controlador" value="parametros">
        <input type="hidden" name="accion" id="accion" value="update">
        <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th style="width: 40%">Parametro</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($parametros["parametros"] as $tmp){
                ?>
                <tr>
                    <td>
                        <?= $tmp["titulo"] ?><br>
                        <small><?= $tmp["descripcion"] ?></small>
                    </td>
                    <td>
                        <? if($tmp["tipo"]==1){ ?>
                        <input type="text" name="<?= $tmp["idparametro"] ?>" value="<?= $tmp["parametro"] ?>" class="form-control">
                        <? }else{ ?>
                        <textarea class="form-control" name="<?= $tmp["idparametro"] ?>" rows="4"><?= $tmp["parametro"] ?></textarea>
                        <? } ?>
                    </td>
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
        <?= $parametros["mensaje"] ?>
    </div>
</div>
<?
}
?>