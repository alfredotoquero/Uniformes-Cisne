<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Contactos.php");

$claseContactos = new Contactos();

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
  function seleccionarContacto(idcontacto,nombre,correo,telefono){
  	$("#idcontacto").val(idcontacto);
	$("#txtContacto").val(nombre);
	$("#txtContacto").attr("readonly","true");
	$("#txtCorreoC").val(correo);
	$("#txtCorreoC").attr("readonly","true");
	$("#txtTelefonoC").val(telefono);
	$("#txtTelefonoC").attr("readonly","true");
	$("#iconBorrar2").removeClass("fa-disabled");
	$("#btnBorrar2").prop("disabled",false);
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


<div style="width:600px: height:400px; padding-left: 0px; padding-right: 0px;">
	<form name="formBusqueda" id="formBusqueda" action="buscarcontacto.php?idcliente=<?= $_GET["idcliente"]; ?>" method="post">
		<!-- <input type="hidden" name="accion" value="busqueda"> -->
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
							<th class="col-10">CONTACTO</th>
							<th class="col-2"></th>
						</tr>
					</thead>
					<tbody>
						<?
						// $contactos = mysqli_query($con,"select * from tcontactos where idcliente = '".$_GET["idcliente"]."'".$filtro." order by nombre");
                        $_POST["idcliente"] = $_GET["idcliente"];
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