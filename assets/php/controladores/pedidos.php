<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $clasePedidos = new Pedidos();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $clasePedidos->agregarPedido($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $clasePedidos->editarPedido($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "activar":
            // $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $clasePedidos->activarPedido($_POST);
        break;
        case "entregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                // $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
                $respuesta = $clasePedidos->entregarPedido($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "finalizar":
            // $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $clasePedidos->finalizarPedido($_POST);
        break;
        case "cancelar":
            $respuesta = $clasePedidos->cancelarPedido($_POST);
        break;
        case "cargardatospedido":
            $respuesta = $clasePedidos->obtenerPedido($_POST)["pedido"];
        break;
        case "reiniciarprecioproductos":
            $respuesta = $clasePedidos->reiniciarPrecioProductosTMP($_POST);
        break;
        case "validardesgloses":
            $respuesta = $clasePedidos->validarDesgloses($_POST);
        break;
        case "validarespecificaciones":
            $respuesta = $clasePedidos->validarEspecificaciones($_POST);
        break;
        case "verificarmovimientospendientes":
            $respuesta = $clasePedidos->verificarMovimientosPendientes($_POST);
        break;
        case "editarsucursal":
            $respuesta = $clasePedidos->editarSucursal($_POST);
            break;
        case "facturar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $clasePedidos->facturarPedido($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
