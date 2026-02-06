<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Tiendas.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseTiendas = new Tiendas();

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTiendas->agregarTienda($_POST,$_FILES);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTiendas->editarTienda($_POST,$_FILES);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseTiendas->eliminarTienda($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>