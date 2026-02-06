<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Catalogos.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseCatalogos = new Catalogos();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch($_POST["accion"]){
        case "agregar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCatalogos->agregarCatalogo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCatalogos->editarCatalogo($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminar":
            $respuesta = $claseCatalogos->eliminarCatalogo($_POST);
        break;
        case "agregarvalor":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCatalogos->agregarValor($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "editarvalor":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCatalogos->editarValor($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "eliminarvalor":
            $respuesta = $claseCatalogos->eliminarValor($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>