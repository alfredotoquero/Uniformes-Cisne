<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pagos.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $clasePagos = new Pagos();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "verPDF":
            $respuesta = $clasePagos->getPDF($_POST["idpago"]);
        break;
        case "cancelarComplemento":
            $respuesta = $clasePagos->cancelarComplemento($_POST);
        break;
        case "cancelar":
            $respuesta = $clasePagos->cancelarPago($_POST);
        break;
        case "timbrar":
            $respuesta = $clasePagos->timbrarPago($_POST);
        break;
        case "verificarEstatusSAT":
            $respuesta = $clasePagos->verificarEstatusSAT($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }

}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
