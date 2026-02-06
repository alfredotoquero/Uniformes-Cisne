<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseCotizaciones = new Cotizaciones();
$claseProductos = new Productos();

$idusuario = $_SESSION["usuario"]["idusuario"];

$_POST["idusuario"] = $idusuario;
$especificaciones = $claseCotizaciones->obtenerEspecificacionesTMP($_POST);

if($especificaciones["respuesta"]=="OK"){
?>
<p style="margin-top: 30px;">Lista de especificaciones agregadas al pedido.</p>
<table class="table m-0">
	<thead>
		<tr>
			<th>Especificacion</th>
			<th width="300">Partidas</th>
			<th width="10"></th>
			<th width="10"></th>
		</tr>
	</thead>
	<tbody>
		<?
		foreach($especificaciones["especificaciones"] as $especificacion){

			$detalle = "";
			$detalle .= $especificacion["nombrediseno"] . "<br>" .
			"Fecha de entrega: " . fecha_formateada($especificacion["fechaentrega"]) . "<br>" .
			($especificacion["serigrafia"]==1 ? "Serigrafia, " : "") .  
			($especificacion["digital"]==1 ? "Digital, " : "") . 
			($especificacion["bordado"]==1 ? "Bordado, " : "") .  
			($especificacion["especificaciones"]!="" ? "Especificaciones: " . $especificacion["especificaciones"] . ", " : "");

			$detalle = rtrim($detalle,", ");

			$nombres = "";
			$_POST["idusuario"] = $idusuario;
			$_POST["idespecificacion"] = $especificacion["idtmp"];
			$partidas = $claseCotizaciones->obtenerPartidasEspecificacionTMP2($_POST);
			foreach ($partidas["partidas"] as $partida) {
				if ($partida["idproducto"]!="0") {
					$_POST["idproducto"] = $partida["idproducto"];
					$nombre = $claseProductos->obtenerProducto($_POST)["producto"]["nombre"];
				} else {
					$nombre = $partida["producto"];
				}
				$nombres .= $nombre . "<br>";
			}

			$nombres = rtrim($nombres,"<br>");
			?>
			<tr>
				<td><?= $detalle ?></td>
				<td><?= $nombres ?></td>
				<td>
					<div class="row align-items-center">
						<div class="col-12 text-right">
							<a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/cotizaciones/editarespecificacion.php?idespecificacion=<?= $especificacion["idtmp"] ?>"><i class="fas fa-pencil-alt"></i></a>
						</div>
					</div>
				</td>
				<td>
					<div class="row align-items-center">
						<div class="col-12 text-right">
							<a href="javascript:;" class="pull-right" onClick="eliminarEspecificacion(<?= $especificacion["idtmp"]; ?>);" style="margin-right:0px;"><i class="fa fa-times"></i></a>
						</div>
					</div>
				</td>
			</tr>
			<?
		}
		?>
	</tbody>
</table>
<?
}else {
	?>
    <b><center>--No hay especificaciones agregadas--</center></b>
    <?
}
?>