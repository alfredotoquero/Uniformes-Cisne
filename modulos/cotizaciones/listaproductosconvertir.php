<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Cotizaciones.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Categorias.php");

$claseCotizaciones = new Cotizaciones();
$claseProductos = new Productos();
$claseCategorias = new Categorias();

$idusuario = $_SESSION["usuario"]["idusuario"];
$_POST["idusuario"] = $_SESSION["usuario"]["idusuario"];

$partidas = $claseCotizaciones->obtenerPartidasTMP($idusuario);

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/txts/prueba.txt",print_r($_POST,true));

$subtotal = 0;
$total = 0;
$iva = 0;
if($partidas["respuesta"]=="OK"){
?>
<p style="margin-top: 30px;">Lista de partidas agregadas a la cotización.</p>
<table class="table m-0">
	<thead>
		<tr>
			<th width="30">Cant.</th>
			<th>Producto</th>
			<th width="100">P.U.</th>
			<th width="100">Importe</th>
			<th width="10"></th>
			<th width="10"></th>
		</tr>
	</thead>
	<tbody>
		<?
		foreach($partidas["partidas"] as $partida){
			$desglosable = false;
            $_POST["idproducto"] = $partida["idproducto"];
			$producto = $claseProductos->obtenerProducto($_POST);
			if ($producto["respuesta"]=="OK") {
				$nombre = $producto["producto"]["nombre"];
			}else {
				$nombre = $partida["producto"];
			}

			if ($_POST["chkIncluyeIva"]==1) {
				$subtotal += (float)($partida["cantidad"]*$partida["precio"]/(1 + ($_POST["slcIVA"]/100)));
				$iva += $partida["cantidad"]*$partida["precio"] - (($partida["cantidad"]*$partida["precio"])/(1 + ($_POST["slcIVA"]/100)));
				$preciounitario = $partida["precio"]/(1 + ($_POST["slcIVA"]/100));
				$partidasubtotal = $partida["cantidad"]*$partida["precio"]/(1 + ($_POST["slcIVA"]/100));
				$partidaiva = $partida["cantidad"]*$partida["precio"] - (($partida["cantidad"]*$partida["precio"])/(1 + ($_POST["slcIVA"]/100)));
				$partidatotal = $partidasubtotal + $partidaiva;
			}else {
				$subtotal += (float)($partida["cantidad"]*$partida["precio"]);
				$iva += ($partida["cantidad"]*$partida["precio"]) * ($_POST["slcIVA"]/100);
				$preciounitario = $partida["precio"];
				$partidasubtotal = $partida["cantidad"]*$partida["precio"];
				$partidaiva = ($partida["cantidad"]*$partida["precio"]) * ($_POST["slcIVA"]/100);
				$partidatotal = $partidasubtotal + $partidaiva;
			}

			$detalle = "";

			$detalle .= $nombre . "<br>" .
			(($partida["serigrafia1"]>0 || $partida["serigrafia2"]>0 || $partida["serigrafia3"]>0) ? "Serigrafía ".$partida["serigrafia1"]." / ".$partida["serigrafia2"]." / ".$partida["serigrafia3"] . ", " : "") .
			($partida["personalizadonumero"]==1 ? "Personalizado Número, " : "") .
			($partida["personalizadonombre"]==1 ? "Personalizado Nombre, " : "") .
			($partida["bordado1"]==1 ? "1 Bordado, " : "") .
			($partida["bordado2"]==1 ? "2 Bordados, " : "") .
			($partida["bordado3"]==1 ? "3 Bordados, " : "") .
			($partida["bordado4"]==1 ? "4 Bordados, " : "") .
			($partida["bordadoespecial"]==1 ? "Bordado Especial, " : "") .
			($partida["personalizado1linea"]==1 ? "Personalizado 1 Linea, " : "") .
			($partida["personalizado2lineas"]==1 ? "Personalizado 2 Lineas, " : "") .
			($partida["personalizado3lineas"]==1 ? "Personalizado 3 Lineas, " : "") .
			($partida["sxl"]==1 ? "S-XL, " : "") .
			($partida["2xl"]==1 ? "2XL, " : "") .
			($partida["3xl"]==1 ? "3XL, " : "") .
			($partida["observaciones"]!="" ? "Observaciones: " . $partida["observaciones"] . ", " : "");

			$detalle = rtrim($detalle,", ");

		?>
		<tr>
			<td align="center"><?= $partida["cantidad"]; ?></td>
			<td><?= $detalle;

            // NOTA: productos que en la tabla tproductoexistencias no tienen talla ni color (idtalla = 0 y idcolor = 0): (211,214,522,742,747,885,888,894)
            if ($partida["idproducto"]>0) {
                // si el producto no maneja existencias, no se le puede agregar desgloses
                if ($claseProductos->manejaExistencias($partida["idproducto"])) {
                    $desglosable = true;
                }
            } else {
                // si el producto es libre, pero su categoria no tiene tallas dadas de alta, entonces no se le puede agregar desgloses
                if ($claseCategorias->tieneTallas($partida["idcategoriaproducto"])) {
                    $desglosable = true;
                }
            }

            if ($desglosable) {
				$_POST["idpartida"] = $partida["idtmp"];
				if (!$claseCotizaciones->asignadoAEspecificacion($_POST)) {
					?>
					<br>
					<br>
					<a href="javascript:;" data-fancybox data-type="ajax" data-src="/modulos/cotizaciones/cargarcolortalla.php?idpartida=<?= $partida["idtmp"]; ?>&idproducto=<?= $partida["idproducto"] ?>&producto=<?= urlencode($partida["producto"]) ?>&idcategoriaproducto=<?= $partida["idcategoriaproducto"] ?>" class="btn btn-primary btn-sm mb-1" title="Desglosar">Desglosar</a>
					<?
				}
                ?>
                <br>
                <br>
                <?
				$_POST["idpartida"] = $partida["idtmp"];
				$desgloses = $claseCotizaciones->obtenerDesglosesPartidaTMP2($_POST);
				foreach($desgloses["desgloses"] as $desglose){
					
					$desglose = $desglose["lista"];
					
					$detalle = "";
	
					$datos = explode(";",$desglose);
					foreach ($datos as $dato) {
						$datos = explode(" : ",$dato);
						$color = $datos[0];
						$tallas = $datos[1];
						$detalle .= $color . " / " . $tallas . "<br>";
					}
					$detalle = rtrim($detalle," /<br>");
						
					echo $detalle;
				}
            }

            
			?></td>
			<td><?= "$".number_format($preciounitario,2); ?></td>
			<td>
				<?
				if($_POST["chkSubtotalizar"]==1){
					echo "$".number_format($partidasubtotal,2);
				}else{
					echo "Subtotal: $".number_format($partidasubtotal,2)."<br>";
					echo "IVA: $".number_format($partidaiva,2)."<br>";
					echo "Total: $".number_format($partidatotal,2);
				}
				?>
			</td>
			<td align="right"><a href="javascript:;" onClick="cargarDatosProducto(<?= $partida["idtmp"]; ?>);"><i class="fas fa-pencil-alt"></i></a></td>
			<td align="right"><a href="javascript:;" onClick="eliminarProductoCotizacion(<?= $partida["idtmp"]; ?>);"><i class="fa fa-times"></i></a></td>

		</tr>
		<?
		}
		$total = (float)($subtotal) + (float)$iva;
		?>
	</tbody>
	<? if($_POST["chkSubtotalizar"]==1){ ?>
	<tfooter>
		<tr id="trSubtotal">
			<td colspan="3" align="right">Subtotal</td>
			<td>$<?= number_format($subtotal,2); ?></td>
			<td></td>
		</tr>
		<tr id="trIva">
			<td colspan="3" align="right">IVA</td>
			<td>$<?= number_format($iva,2); ?></td>
			<td></td>
		</tr>
		<tr id="trTotal">
			<td colspan="3" align="right">Total</td>
			<td>$<?= number_format($total,2); ?></td>
			<td></td>
		</tr>
	</tfooter>
	<? } ?>
</table>
<?
}else {
    ?>
    <b><center>--No hay partidas agregadas--</center></b>
    <?
}
?>
<script>
    $("#subtotal").val(<?= $subtotal; ?>);
    $("#iva").val(<?= $iva; ?>);
    $("#total").val(<?= $total; ?>);
</script>