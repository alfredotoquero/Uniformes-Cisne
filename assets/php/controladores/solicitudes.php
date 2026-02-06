<?php
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Solicitudes.php");

$arrayerror = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => "Acción no permitida.", "post" => $_POST);

try {
    $claseSolicitudes = new Solicitudes();

    $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

    switch ($_POST["accion"]) {
        case "solicitarProductos":
            $respuesta = $claseSolicitudes->solicitarProductos($_POST);
            break;
        case "generarsolicitudes":
            if ($_POST["authToken"] == $_SESSION["authToken"]) {
                $respuesta = $claseSolicitudes->generarSolicitudes($_POST);
            } else {
                $respuesta = $arrayerror;
            }
            break;
        case "validarsolicitudes":
            $respuesta = $claseSolicitudes->validarSolicitudes($_POST);
            break;
        case "validarnombreunico":
            $respuesta = $claseSolicitudes->validarNombreUnicoProducto($_POST);
            break;
        case "dividir":
            if ($_POST["authToken"] == $_SESSION["authToken"]) {
                $respuesta = $claseSolicitudes->dividirCantidad($_POST);
            } else {
                $respuesta = $arrayerror;
            }
            break;
        case "agregarpartida":
            $respuesta = $claseSolicitudes->agregarPartidaTMP($_POST);
            break;
        case "editarpartida":
            $respuesta = $claseSolicitudes->editarPartidaTMP($_POST);
            break;
        case "eliminarpartida":
            $respuesta = $claseSolicitudes->eliminarPartidaTMP($_POST);
            break;
        case "cargardatos":
            $respuesta = $claseSolicitudes->cargarDatos($_POST);
            break;
        case "asignarproveedor":
            $respuesta = $claseSolicitudes->asignarProveedor($_POST);
            break;
        case "generarordencompra":
            // $_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];
            $respuesta = $claseSolicitudes->generarOrdenCompra($_POST);
            break;
        case "eliminarsolicitudes":
            $respuesta = $claseSolicitudes->eliminarSolicitudes($_POST);
            break;
        default:
            $respuesta = $arrayerror;
            break;
    }
} catch (Exception $e) {
    $respuesta = array("respuesta" => "ERROR", "tipo" => "mensaje", "mensaje" => $e->getMessage());
} finally {
    echo json_encode($respuesta, JSON_FORCE_OBJECT);
}
