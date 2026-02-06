<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Notificaciones.php");

$claseNotificaciones = new Notificaciones();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["tipousuario"] = "U";
$notificaciones = $claseNotificaciones->obtenerNotificaciones($_POST);

$claseNotificaciones->marcarComoNotificadas($_POST);
$claseNotificaciones->marcarComoLeidas($_POST);

if($notificaciones["respuesta"]=="OK"){
?>
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="table-dark">
            <tr>
                <th>Notificacion</th>
                <th>Tiempo</th>
                <th style="width: 120px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($notificaciones["notificaciones"] as $notificacion){
                $tiempo = (strtotime(date("Y-m-d H:i:s"))-strtotime($notificacion["created_at"]))/60;
                $tiempo = abs($tiempo); $tiempo = floor($tiempo);

                if($tiempo<60){
                    $tiempo_transcurrido = $tiempo." mins.";
                }else{
                    $tiempo = floor($tiempo/60);
                    if($tiempo<24){
                        $tiempo_transcurrido = $tiempo." hrs.";
                    }else{
                        $tiempo = floor($tiempo/24);
                        $tiempo_transcurrido = $tiempo." dias";
                    }
                }
                ?>
                <tr>
                    <td><?= "<b>".$notificacion["titulo"]."</b><br><small>".$notificacion["mensaje"]."</small>" ?></td>
                    <td><?= $tiempo_transcurrido; ?></td>
                    <td>
                        <a href="javascript:;" onclick="marcarAtendida(this,0,'notificaciones','marcar_como_atendida','idnotificacion=<?= $notificacion['idnotificacion'] ?>','','cargar','',400);" data-toggle="tooltip" title="marcar como atendida" class="btn btn-success btn-sm btnNotificacion" id=""><i class="uil uil-check"></i></a>
                    </td>
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
        <?= $notificaciones["mensaje"] ?>
    </div>
</div>
<?
}
?>