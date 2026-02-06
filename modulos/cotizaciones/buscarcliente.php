<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

// if($_POST["accion"]=="busqueda"){
// 	$filtro = " where nombre like '%".$_POST["txtNombre"]."%'";
// }
?>

<style>
	.fa-disabled {
		opacity: 0.6;
		cursor: not-allowed;
	}

	button{
		background:none;
		border:none;
		font-size:1em;
		cursor: pointer;
	}
</style>

<div style="width: 600px; height: 400px; padding-left: 20px; padding-right: 20px;">
	<form name="formBusqueda" id="formBusqueda">
		<!-- <input type="hidden" name="controlador" id="controlador" value="clientes"> -->
		<!-- <input type="hidden" name="archivo" id="archivo" value="/modulos/cotizaciones/listaclientes.php">
		<input type="hidden" name="contenedor" id="contenedor" value="listaClientes"> -->
		<div class="row" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="col-2">Nombre:</div>
			<div class="col-7"><input type="text" name="txtNombre" id="txtNombre" class="form-control" placeholder="Ingresa un nombre" onkeyup="buscarCliente();"></div>
			<div class="col-3">
				<button type="button" onclick="buscarCliente();" id="btnBuscar1"><i class="fas fa-search" id="iconBuscar1"></i></button>
				<button type="button" onclick="borrarCliente();" style="margin-left:15px;" id="btnBorrar1"><i class="fas fa-eraser" id="iconBorrar1"></i></button>
			</div>
		</div>
	</form>
	<div class="box">
		<div class="box-body">
			<div class="table-responsive">
				<table class="table table-striped b-t">
					<thead>
						<tr>
							<th class="col-10">CLIENTE</th>
							<th class="col-2"></th>
						</tr>
					</thead>
					<tbody id="listaClientes">
						<?
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
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>