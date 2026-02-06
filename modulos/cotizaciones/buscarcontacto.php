<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Contactos.php");

$claseContactos = new Contactos();

$_POST["idcliente"] = $_GET["idcliente"];
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
		<input type="hidden" name="idcliente" id="idcliente" value="<?= $_GET["idcliente"]; ?>">
		<!-- <input type="hidden" name="accion" value="busqueda"> -->
		<div class="row" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="col-2">Nombre:</div>
			<div class="col-8"><input type="text" name="txtNombre" id="txtNombre" class="form-control" placeholder="Ingresa un nombre" onkeyup="buscarContacto();"></div>
			<div class="col-2">
				<button type="button" onclick="buscarContacto();" id="btnBuscar1"><i class="fas fa-search" id="iconBuscar1"></i></button>
				<button type="button" onclick="borrarContacto();" style="margin-left:15px;" id="btnBorrar1"><i class="fas fa-eraser" id="iconBorrar1"></i></button>
			</div>
		</div>
	</form>
	<div class="box">
		<div class="box-body">
			<div class="table-responsive">
				<table class="table table-striped b-t">
					<thead>
						<tr>
							<th class="col-10">CONTACTO</th>
							<th class="col-2"></th>
						</tr>
					</thead>
					<tbody id="listaContactos">
						<?
						// $contactos = mysqli_query($con,"select * from tcontactos where idcliente = '".$_GET["idcliente"]."'".$filtro." order by nombre");
						$contactos = $claseContactos->obtenerContactos($_POST);
						// while($contacto = mysqli_fetch_assoc($contactos)){
						foreach($contactos["contactos"] as $contacto){
						?>
						<tr>
							<td><?= $contacto["nombre"]; ?></td>
							<td><button type="button" onClick="seleccionarContacto('<?= $contacto["idcontacto"]; ?>','<?= $contacto["nombre"]; ?>','<?= $contacto["correo"]; ?>','<?= $contacto["telefono"]; ?>');" class="btn btn-primary btn-sm">SELECCIONAR</button></td>
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