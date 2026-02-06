<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseMovimientos = new Movimientos();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseMovimientos->agregarMovimiento($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "recibir":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseMovimientos->recibirProductos($_POST);
                unset($_SESSION["authToken"]);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "cancelar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseMovimientos->cancelarMovimiento($_POST);
                unset($_SESSION["authToken"]);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "autorizar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseMovimientos->actualizarAutorizacionMovimiento($_POST);
                unset($_SESSION["authToken"]);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "noautorizar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseMovimientos->actualizarAutorizacionMovimiento($_POST);
                unset($_SESSION["authToken"]);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "agregarpartida":
            $respuesta = $claseMovimientos->agregarPartidaTMP($_POST);
        break;
        case "editarpartida":
            $respuesta = $claseMovimientos->editarPartidaTMP($_POST);
        break;
        case "eliminarpartida":
            $respuesta = $claseMovimientos->eliminarPartidaTMP($_POST);
        break;
        case "validarexistenciasalmacen":
            $respuesta = $claseMovimientos->validarExistenciasAlmacen($_POST);
        break;
        case "validarmovimiento":
            $respuesta = $claseMovimientos->validarMovimiento($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>