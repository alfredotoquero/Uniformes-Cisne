<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");

$claseProveedores = new Proveedores();
$claseSolicitudes = new Solicitudes();

?>
<option value="-1">--Selecciona un Proveedor--</option>
<?
// agregar tantos botones como proveedores haya
$proveedores = $claseProveedores->obtenerProveedores($_POST);
foreach ($proveedores["proveedores"] as $proveedor) {
    // calcula la cantidad de productos que se necesitan comprar para este proveedor
    $_POST["idproveedor"] = $proveedor["idproveedor"];
    $total = $claseSolicitudes->obtenerTotalSolicitudes($_POST)["total"];
    if($total>0){
        ?>
            <option value="<? echo $proveedor["idproveedor"]; ?>" <? if($proveedor["idproveedor"]==$_POST["miidproveedor"]){?> selected <?} ?>><? echo $proveedor["nombre"] . " (" . $total . ")"; ?></option>
        <?

    }
}
$_POST["idproveedor"] = 0;
$total = $claseSolicitudes->obtenerTotalSolicitudes($_POST)["total"];
if ($total>0) {
    ?>
        <option value="0" <? if($_POST["miidproveedor"]==0){?> selected <?} ?>><? echo "Otros " . " (" . $total . ")"; ?></option>
    <?
}
?>
