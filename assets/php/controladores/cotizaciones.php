<?php
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");

$arrayerror = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>"Acción no permitida.");

try{
    $claseCotizaciones = new Cotizaciones();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "guardar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCotizaciones->guardarCotizacion($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "convertir":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCotizaciones->convertirAPedido($_POST);
            }else{
                $respuesta = $arrayerror;
            }
        break;
        case "cancelar":
            $respuesta = $claseCotizaciones->cancelarCotizacion($_POST);
        break;
        case "cargardatoscotizacion":
            $respuesta = $claseCotizaciones->obtenerCotizacion($_POST)["cotizacion"];
        break;
        case "validarnombreunico":
            $respuesta = $claseCotizaciones->validarNombreUnicoProducto($_POST);
        break;
        case "agregarpartida":
            // if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCotizaciones->agregarPartidaTMP($_POST);
            // }else{
                // $respuesta = $arrayerror;
            // }
        break;
        case "editarpartida":
            // if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseCotizaciones->editarPartidaTMP($_POST);
            // }else{
                // $respuesta = $arrayerror;
            // }
        break;
        case "eliminarpartida":
            $respuesta = $claseCotizaciones->eliminarPartidaTMP($_POST["idpartida"]);
        break;
        case "recuperarespecificaciones":
            $respuesta = $claseCotizaciones->recuperarEspecificacionesTMP($_POST["idpartida"]);
        break;
        case "cargardatospartida":
            $respuesta = $claseCotizaciones->obtenerPartidaTMP($_POST["idpartida"])["partida"];
        break;
        case "validarcorreorepetido":
            $respuesta = $claseCotizaciones->validarCorreoRepetido($_POST["correo"]);
        break;
        case "validarcantidades":
            $respuesta = $claseCotizaciones->validarCantidades($_POST);
        break;
        case "guardardesglose":
            // if ($_POST["authToken"]==$_SESSION["authToken"]) {
                $respuesta = $claseCotizaciones->guardarDesgloseTMP($_POST);
            // } else {
            //     $respuesta = $arrayerror;
            // }
        break;
        case "insertarimagenestmp":
            // crear la carpeta donde se almacenaran temporalmente las imagenes ()
            $ruta = "/imagenes/usuarioesptmp/".$idusuario;
            mkdir($_SERVER["DOCUMENT_ROOT"].$ruta,0777,true);
            $claseCotizaciones->subirImagenTMP($_FILES["img1"],$ruta."/","1".$_FILES["img1"]["name"],200);
            $claseCotizaciones->subirImagenTMP($_FILES["img2"],$ruta."/","2".$_FILES["img2"]["name"],200);
            $claseCotizaciones->subirImagenTMP($_FILES["img3"],$ruta."/","3".$_FILES["img3"]["name"],200);
            $claseCotizaciones->subirImagenTMP($_FILES["img4"],$ruta."/","4".$_FILES["img4"]["name"],200);
            $claseCotizaciones->subirImagenTMP($_FILES["img5"],$ruta."/","5".$_FILES["img5"]["name"],200);
            // $claseCotizaciones->subirImagenTMP($idusuario,$_FILES["img1"],1);
            // $claseCotizaciones->subirImagenTMP($idusuario,$_FILES["img2"],2);
            // $claseCotizaciones->subirImagenTMP($idusuario,$_FILES["img3"],3);
            // $claseCotizaciones->subirImagenTMP($idusuario,$_FILES["img4"],4);
            // $claseCotizaciones->subirImagenTMP($idusuario,$_FILES["img5"],5);
            // respuesta ok
            $respuesta = array("respuesta"=>"OK");
        break;
        case "asignardesglose":
            $respuesta = $claseCotizaciones->asignarDesgloseTMP($_POST);
        break;
        case "reasignardesglose":
            $respuesta = $claseCotizaciones->reasignarDesgloseTMP($_POST);
        break;
        case "eliminarespecificacion":
            $respuesta = $claseCotizaciones->eliminarEspecificacionTMP($_POST["idespecificacion"]);
        break;
        case "validardesgloses":
            $respuesta = $claseCotizaciones->validarDesgloses($_POST);
        break;
        case "validarespecificaciones":
            $respuesta = $claseCotizaciones->validarEspecificaciones($_POST);
        break;

        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array("respuesta"=>"ERROR","tipo"=>"mensaje","mensaje"=>$e->getMessage());
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
?>