<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Facturas.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseFacturas = new Facturas();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "verPDF":
            $respuesta = $claseFacturas->getPDF($_POST["idfactura"]);
        break;
        case "cancelar":
            $respuesta = $claseFacturas->cancelarFactura($_POST);
        break;
        case "reenviar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseFacturas->reenviarFactura($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "refacturar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseFacturas->refacturarFactura($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "verificarEstatusSAT":
            $respuesta = $claseFacturas->verificarEstatusSAT($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
