<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
// include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$claseMovimientos = new Movimientos();

$idusuario = $_SESSION["usuario"]["idusuario"];

$partidas = $claseMovimientos->obtenerPartidasTMP2($idusuario);

if ($partidas["respuesta"]=="OK"){
    

	?>
	<script>
	if ($("#slcTipoMovimiento").is(":enabled")) {
		if ($("#divAlmacenSec").is(":visible")) {
			$("#slcAlmacenS").prop("disabled",true);
		}
		$("#slcTipoMovimiento").prop("disabled",true);
		$("#slcAlmacen").prop("disabled",true);
	}
	</script>

	<div class="box">
		<div class="box-header">
			<p style="margin-top: 30px;">Lista de productos agregados.</p>
		</div>
		<div class="box-body">
			<table class="table m-0">
				<thead>
					<tr>
						<th>Producto</th>
						<th width="10"></th>
						<th width="10"></th>
					</tr>
				</thead>
				<tbody>
					<?
					// while($partida = mysqli_fetch_assoc($partidas)){
					foreach($partidas["partidas"] as $partida){
						// file_put_contents($_SERVER["DOCUMENT_ROOT"]."/prueba.txt","entra2");
						
						$productos = $partida["lista"];
						$datos1 = explode(" : ",$productos);
						$nombreproducto = $datos1[0];
						
						$detalle = "";
						$detalle .= $nombreproducto . "<br>";
						// $producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
						// si el producto es libre, entonces el nombre, talla y color no vienen de la tabla, sino de lo que se insertó ($partida["producto"], $partida["talla"] y $partida["color"])
						?>
					<tr>
						<td>
							<? 

							
							$datos2 = explode("-_-",$datos1[1]);
							foreach ($datos2 as $dato) {
								$detalle .= $dato . "<br>";
							}

							// if (mysqli_num_rows($producto)>0) {
								// $nombre = mysqli_fetch_assoc($producto)["nombre"];
								// echo $nombre . "<br>";

								// $dcolores = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$partida["idpartida"]."' and idusuario='".$_SESSION["4dm1npl4y3r4sc1sn3usr"]."' group by idcolor having idcolor!=0");
								// if (mysqli_num_rows($dcolores)>0) {
									// while ($dcolor = mysqli_fetch_assoc($dcolores)) {
									// 	$color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
									// 	echo $color;
									// 	$dtallas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$partida["idpartida"]."' and idusuario='".$_SESSION["4dm1npl4y3r4sc1sn3usr"]."' and idcolor='".$dcolor["idcolor"]."'");
									// 	while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
									// 		// talla
									// 		$talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
									// 		echo " / " . $dtalla["cantidad"] . " - " . $talla;
									// 	}
										?>
										<!-- <br> -->
										<? 
									// }
								// }
								
								echo $detalle;
							// }
							
				
							?>
						</td>
						<td align="right"><a href="javascript:;" onClick="cargarDatosMovimiento(<? echo $partida["idpartida"]; ?>,<? echo $partida["idproducto"]; ?>);"><i class="fas fa-pencil-alt"></i></a></td>
						<td align="right"><a href="javascript:;" onClick="eliminarProductoMovimiento(<? echo $partida["idpartida"]; ?>);"><i class="fa fa-times"></i></a></td>

					</tr>
					<?
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?
}else {
	?>
	<script>
	$("#slcTipoMovimiento").prop("disabled",false);
	$("#slcAlmacen").prop("disabled",false);
	if ($("#divAlmacenSec").is(":visible")) {
		$("#slcAlmacenS").prop("disabled",false);
	}
	</script>
	<center><p><b>--No hay productos agregados--</b></p></center>
	<?
}
?>