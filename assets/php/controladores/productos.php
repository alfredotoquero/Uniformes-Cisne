<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseProductos = new Productos();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->agregarProducto($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->editarProducto($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignaralmacenes":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->asignarAlmacenes($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignarcoloresytallas":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->asignarColoresYTallas($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignarcatalogos":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->asignarCatalogos($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "guardarvariantes":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->guardarVariantes($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "actualizarstockminimo":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProductos->actualizarStockMinimo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseProductos->eliminarProducto($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>