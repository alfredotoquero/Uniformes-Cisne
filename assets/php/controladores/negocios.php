<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Negocios.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseNegocios = new Negocios();

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseNegocios->agregarNegocio($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseNegocios->editarNegocio($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseNegocios->eliminarNegocio($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>