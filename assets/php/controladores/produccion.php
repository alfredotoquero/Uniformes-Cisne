<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Produccion.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseProduccion = new Produccion();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->agregarProduccion($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->editarProduccion($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "asignarproductos":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->asignarProductos($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        // case "iniciardiseno":
        //     $respuesta = $claseProduccion->iniciarDiseno($_POST);
        // break;
        case "enviardiseno":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->enviarDiseno($_POST,$_FILES);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "aprobardiseno":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->aprobarDiseno($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "rechazardiseno":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->rechazarDiseno($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        // case "terminardiseno":
        //     $respuesta = $claseProduccion->terminarDiseno($_POST);
        // break;
        case "asignarproduccion":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->asignarProduccion($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "finalizarproduccion":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseProduccion->finalizarProduccion($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "terminarproduccion":
            $respuesta = $claseProduccion->terminarProduccion($_POST);
        break;
        case "eliminar":
            $respuesta = $claseProduccion->eliminarProduccion($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
// Verificar si terminar produccion pasa por los 4 tipos (partida terminada, especificacion terminada, pedido terminado, cliente terminado)
// Seguir agregando especificaciones al cliente y verificar los tres status para ver si corresponden
?>