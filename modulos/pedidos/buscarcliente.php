<?
include("../../vm39845um223u/c91ktn24g7if5u.php");
include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Clientes.php");

$claseClientes = new Clientes();

// if($_POST["accion"]=="busqueda"){
// 	$filtro = " where nombre like '%".$_POST["txtBusqueda"]."%'";
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
  
  <script>
  function seleccionarCliente(idcliente,nombre,correo,telefono){
	// se llenan los datos del cliente
  	$("#idcliente").val(idcliente);
	$("#txtCliente").val(nombre);
	$("#txtCliente").attr("readonly","true");
	$("#txtCorreo").val(correo);
	$("#txtCorreo").attr("readonly","true");
	$("#txtTelefono").val(telefono);
	$("#txtTelefono").attr("readonly","true");
	$("#iconBorrar1").removeClass("fa-disabled");
	$("#btnBorrar1").prop("disabled",false);
	// se deben borrar los datos de contacto
	$("#txtContacto").val("");
	document.getElementById('txtContacto').removeAttribute('readonly');
	$("#txtCorreoC").val("");
	document.getElementById('txtCorreoC').removeAttribute('readonly');
	$("#txtTelefonoC").val("");
	document.getElementById('txtTelefonoC').removeAttribute('readonly');
	$("#iconBorrar2").addClass("fa-disabled");
	$("#btnBorrar2").prop("disabled",true);
	// se quita el checkbox de no guardar datos del cliente, ya que es un cliente registrado
	$("#chkNoGuardarCliente").prop('checked', false);
	$("#divGuardarCliente").hide();
	$.fancybox.close();
  }
  
  function buscar(){
  	if($("#txtBusqueda").val().trim()==""){
		alert("ATENCION: Debes ingresar un nombre válido.");
		$("#txtBusqueda").focus();
	}else{
		$("#formBusqueda").submit();
	}
  }
  
  function borrar(){
  	location.href = location.href;
  }
  </script>

<div style="width:600px; height:400px; padding-left: 0px; padding-right: 0px;">
	<form name="formBusqueda" id="formBusqueda" action="" method="post">
		<!-- <input type="hidden" name="accion" id="accion" value="busqueda"> -->
		<div class="row" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="col-2">Nombre:</div>
			<div class="col-8"><input type="text" name="txtBusqueda" id="txtBusqueda" value="<?= $_POST["txtBusqueda"]; ?>" class="form-control" placeholder="Ingresa un nombre"></div>
			<div class="col-2">
				<button type="button" onclick="buscar();" id="btnBuscar1"><i class="fas fa-search" id="iconBuscar1"></i></button>
				<button type="button" onclick="borrar();" style="margin-left:15px;" id="btnBorrar1"><i class="fas fa-eraser" id="iconBorrar1"></i></button>
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
					<tbody>
						<?
						// $clientes = mysqli_query($con,"select * from tclientes".$filtro." order by nombre");
						$clientes = $claseClientes->obtenerClientes($_POST);
						// while($cliente = mysqli_fetch_assoc($clientes)){
						foreach ($clientes["clientes"] as $cliente){
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
