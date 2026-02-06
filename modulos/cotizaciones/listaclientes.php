<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

// $clientes = mysqli_query($con,"select * from tclientes".$filtro." order by nombre");
$clientes = $claseClientes->obtenerClientes($_POST);
// while($cliente = mysqli_fetch_assoc($clientes)){
foreach($clientes["clientes"] as $cliente){
?>
<tr>
    <td><?= $cliente["nombre"]; ?></td>
    <td><button type="button" onClick="seleccionarCliente('<?= $cliente["idcliente"]; ?>','<?= $cliente["nombre"]; ?>','<?= $cliente["correo"]; ?>','<?= $cliente["telefono"]; ?>');" class="btn btn-primary btn-sm">SELECCIONAR</button></td>
</tr>
<?
}
?>