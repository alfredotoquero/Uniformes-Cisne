<?php

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Notificaciones.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseNotificaciones = new Notificaciones();

    $_POST["tipousuario"] = "U";

    switch($_POST["accion"]){
        case "marcar_como_atendida":
            $respuesta = $claseNotificaciones->marcarComoAtendida($_POST);
        break;
        case "marcar_como_atendidas":
            $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $claseNotificaciones->marcarComoAtendidas($_POST);
        break;
        case "marcar_diez_como_leidas":
            $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $_POST["tipousuario"] = "U";
            $respuesta = $claseNotificaciones->marcarDiezComoLeidas($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>