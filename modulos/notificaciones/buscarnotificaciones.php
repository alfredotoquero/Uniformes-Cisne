<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

header('Content-type: application/json',true);

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Notificaciones.php");
$claseNotificaciones = new Notificaciones();

$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
$_POST["tipousuario"] = "U";
$_POST["sinleer"] = 0;

if ($_POST["tipo"]=="toast") {
    // recuperar todas las notificaciones que hayan sido generadas para este usuario en los ultimos 30 segundos
    $_POST["obtenerrecientes"] = 1;
    $notificaciones = $claseNotificaciones->obtenerNotificaciones($_POST);

    if ($notificaciones["respuesta"]=="OK") {
        $arrayNotificaciones = array();
        foreach ($notificaciones["notificaciones"] as $notificacion) {
            $claseNotificaciones->marcarComoNotificada($notificacion["idnotificacion"]);
            array_push($arrayNotificaciones,$notificacion);
        }
    
        $respuesta = array("respuesta"=>"OK","notificaciones"=>$arrayNotificaciones);    
    }else {
        $respuesta = array("respuesta"=>"error","mensaje"=>"Sin notificaciones nuevas por toast");
    }

} else if ($_POST["tipo"]=="campana") {

    $arrayNotificaciones = array();
    $notificaciones = $claseNotificaciones->obtenerNotificaciones($_POST);
    foreach ($notificaciones["notificaciones"] as $notificacion){
        if($notificacion["notificada"]==0){
            $fecha = date("Y-m-d H:i:s");

            $_POST["idnotificacion"] = $notificacion["idnotificacion"];
            $claseNotificaciones->marcarComoNotificada($_POST);
        }else{
            $fecha = $notificacion["created_at"];
        }

        $tiempo = (strtotime(date("Y-m-d H:i:s"))-strtotime($fecha))/60;
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

        array_push($arrayNotificaciones,array("titulo"=>$notificacion["titulo"],"notificacion"=>$notificacion["mensaje"],"idtiponotificacion"=>$notificacion["idtiponotificacion"],"idorigen"=>$notificacion["idorigen"],"tiempo_transcurrido"=>$tiempo_transcurrido));
    }

    $respuesta = array("respuesta"=>"OK","notificaciones"=>$arrayNotificaciones,"cant_notificaciones"=>$notificaciones["cant_notificaciones"],"cant_notificaciones_no_leidas"=>$notificaciones["cant_notificaciones_no_leidas"]);
}

// $respuesta = array("n"=>0);

echo json_encode($respuesta);
?>