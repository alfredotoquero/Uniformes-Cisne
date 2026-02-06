<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

header('Content-type: application/json',true);

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
$claseProveedores = new Proveedores();

$proveedoresf = $claseProveedores->obtenerProveedores($_POST)["proveedores"];

$proveedores = array();
foreach ($proveedoresf as $proveedor) {
    array_push($proveedores,$proveedor);
}

$respuesta = array("respuesta"=>"OK","proveedores"=>$proveedores);

// return $proveedores;

echo json_encode($respuesta);
?>