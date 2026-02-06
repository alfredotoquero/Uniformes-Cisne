<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseClientes = new Clientes();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                if($_POST["idusuario"]!=3){
                    $respuesta = $claseClientes->agregarClienteNEW($_POST);
                }else{
                    $respuesta = $claseClientes->agregarClienteNEW($_POST);
                }
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseClientes->editarCliente($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseClientes->eliminarCliente($_POST);
        break;
        case "buscarcliente":
            $respuesta = $claseClientes->obtenerCliente($_POST);
        break;
        case "agregarrazon":
            $respuesta = $claseClientes->agregarRazonSocial($_POST);
        break;
        case "editarrazon":
            $respuesta = $claseClientes->editarRazonSocial($_POST);
        break;
        case "eliminarrazon":
            $respuesta = $claseClientes->eliminarRazonSocial($_POST);
        break;
        case "cargarrazonsocial":
            $respuesta = $claseClientes->obtenerRazonSocial($_POST["idrazonsocial"]);
        break;
        case "agregarseguimiento":
            $respuesta = $claseClientes->agregarSeguimiento($_POST);
        break;
        case "responderseguimiento":
            $respuesta = $claseClientes->responderSeguimiento($_POST);
        break;
        case "agregartarea":
            $respuesta = $claseClientes->agregarTarea($_POST);
        break;
        case "editartarea":
            $respuesta = $claseClientes->editarTarea($_POST);
        break;
        case "finalizartarea":
            $respuesta = $claseClientes->finalizarTarea($_POST);
        break;
        case "cargartarea":
            $respuesta = $claseClientes->obtenerTarea($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>