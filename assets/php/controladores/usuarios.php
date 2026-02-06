<?php

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseUsuarios = new Usuarios();
    
    $_POST["miidusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "login":
            
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseUsuarios->iniciarSesion($_POST);
                if($respuesta["respuesta"]=="OK"){
                    $_SESSION["usuario"] = $respuesta["usuario"];
                    $_SESSION["fecha"] = date("Y-n-j H:i:s");
                    unset($_SESSION["authToken"]);
                }
            }else{
                $arrayerror["tokens"] = $_POST["authToken"]." - ".$_SESSION["authToken"];
                $respuesta = $arrayerror;
            }
        break;
        case "logout":
            unset($_SESSION["authToken"]);
            unset($_SESSION["usuario"]);
            unset($_SESSION["fecha"]);
            $respuesta = array("respuesta"=>"OK");
        break;
        case "validarpassword":
            $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $claseUsuarios->validarPassword($_POST);
        break;
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseUsuarios->agregarUsuario($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseUsuarios->editarUsuario($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseUsuarios->deshabilitarUsuario($_POST);
        break;
        case "asignarpermisos":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseUsuarios->asignarPermisos($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignaralmacenes":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseUsuarios->asignarAlmacenes($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>