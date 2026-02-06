<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/TipoCambio.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseTipoCambio = new TipoCambio();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
    
    switch($_POST["accion"]){
        case "actualizar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTipoCambio->actualizarTipoCambio($_POST);
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