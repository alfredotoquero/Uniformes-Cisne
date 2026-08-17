<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/NotasCredito.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseNotasCredito = new NotasCredito();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "crear":
            // El authToken evita que se timbre dos veces la misma nota si el usuario
            // reenvía el formulario, igual que en refacturar y reenviar
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseNotasCredito->crearNotaCredito($_POST);

                // El token solo se consume cuando la nota quedó timbrada: si falló una
                // validación el usuario corrige y vuelve a enviar el mismo formulario
                if($respuesta["respuesta"]=="OK"){
                    unset($_SESSION["authToken"]);
                }
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "verPDF":
            $respuesta = $claseNotasCredito->getPDF($_POST["idnotacredito"]);
        break;
        default: $respuesta = $arrayerror; break;
    }

}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
