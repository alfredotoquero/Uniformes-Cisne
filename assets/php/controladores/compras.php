<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Compras.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseCompras = new Compras();
    
    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "recibirproducto":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCompras->recibirProducto($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignarproducto":
            // $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $claseCompras->asignarProducto($_POST);
        break;
        case "asignarproductos":
            $respuesta = $claseCompras->asignarProductos($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>