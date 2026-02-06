<?
$_SERVER['DOCUMENT_ROOT'] = "/home/jorgur2/uniformescisne.mx/1.uniformescisne.mx";
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

$claseClientes->obtenerNotificacionesRecordatorios();

?>