<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TarjetasRegalo.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseTarjetasRegalo = new TarjetasRegalo();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTarjetasRegalo->agregarTarjetaRegalo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "activar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTarjetasRegalo->activarTarjetasRegalo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "reactivar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTarjetasRegalo->reactivarTarjetasRegalo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "reactivarventa":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTarjetasRegalo->reactivarVentaTarjetasRegalo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "agregarexcel":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTarjetasRegalo->agregarTarjetasExcel($_FILES);
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