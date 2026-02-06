<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Emisores.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseEmisores = new Emisores();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseEmisores->agregarEmisor($_POST,$_FILES);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseEmisores->editarEmisor($_POST,$_FILES);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseEmisores->eliminarEmisor($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>