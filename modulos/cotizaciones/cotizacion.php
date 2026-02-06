<?

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Cotizaciones.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");

$claseCotizaciones = new Cotizaciones();
$claseSucursales = new Sucursales();

require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf/fpdf.php");

// $cotizacion = mysqli_fetch_assoc(mysqli_query($con,"select * from vcotizaciones where idcotizacion = '".$_GET["idcotizacion"]."'"));
$_POST["idcotizacion"] = $_GET["idcotizacion"];
$cotizacion = $claseCotizaciones->obtenerCotizacion($_POST)["cotizacion"];

// Creación del objeto de la clase heredada
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);

// $pdf->Image("../../assets/images/membreteuniformes.jpg",0,0,220,280);
$formato = $claseSucursales->obtenerSucursal(array("idsucursal" => $cotizacion["idsucursal"]))["sucursal"]["formato"];
if(!is_null($formato) && file_exists($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato)){
	$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato, 0, 0, 220, 280);
}else{
	$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/membreteuniformes.jpg", 0, 0, 220, 280);
}

$pdf->SetY(45);
$pdf->Cell(0, 0, mb_convert_encoding(fecha_formateada_largo(explode(" ", $cotizacion["fecha"])[0]), 'latin1', 'UTF-8'), 0, 0, 'R');

$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(10);
$pdf->Cell(25, 4, mb_convert_encoding("Cotización #" . $cotizacion["idcotizacion"], 'latin1', 'UTF-8'), 'B', 0, 'L');

$pdf->SetFont('Helvetica', '', 9);
$pdf->Ln(10);
// $pdf->Cell(0,0,mb_convert_encoding('Attn: ---'.(($cotizacion["cliente"]!="") ? $cotizacion["cliente"] : "Público en general").'---'),0,0,'L');
$pdf->Cell(0, 0, mb_convert_encoding('Attn: ' . (($cotizacion["contacto"] != "") ? $cotizacion["contacto"] . " / " . $cotizacion["cliente"] : (($cotizacion["cliente"] != "") ? $cotizacion["cliente"] : "Público en general")), 'latin1', 'UTF-8'), 0, 0, 'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(0, 0, mb_convert_encoding('Atendiendo su petición extendemos la presente para poner a su consideración nuestros servicios.', 'latin1', 'UTF-8'), 0, 0, 'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(106, 5, mb_convert_encoding('Concepto', 'latin1', 'UTF-8'), '', 0, 'L');
$pdf->Cell(20, 5, mb_convert_encoding('Cantidad', 'latin1', 'UTF-8'), '', 0, 'C');
$pdf->Cell(35, 5, mb_convert_encoding('P. Unitario', 'latin1', 'UTF-8'), '', 0, 'R');
$pdf->Cell(35, 5, mb_convert_encoding('Total', 'latin1', 'UTF-8'), '', 0, 'R');
$pdf->Ln(5);

$num = 1;
// $partidas = mysqli_query($con,"select * from trcotizacionproductos where idcotizacion = '".$cotizacion["idcotizacion"]."' and idpedido=0");
$partidas = $claseCotizaciones->obtenerPartidas($_POST);
// while($partida = mysqli_fetch_assoc($partidas)){
foreach ($partidas["partidas"] as $partida) {
	// $producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
	// if (mysqli_num_rows($producto)>0) {
	// 	$nombre = mysqli_fetch_assoc($producto)["nombre"];
	// } else {
	// 	$nombre = $partida["producto"];
	// }
	$nombre = $partida["producto"];

	if ($cotizacion["incluyeiva"] == 1) {
		$preciounitario = $partida["precio"] / ($cotizacion["tasaiva"] / 100 + 1);
		$partidasubtotal = $partida["cantidad"] * $partida["precio"] / ($cotizacion["tasaiva"] / 100 + 1);
		$partidaiva = $partida["cantidad"] * $partida["precio"] - (($partida["cantidad"] * $partida["precio"]) / ($cotizacion["tasaiva"] / 100 + 1));
		$partidatotal = $partidasubtotal + $partidaiva;
	} else {
		$preciounitario = $partida["precio"];
		$partidasubtotal = $partida["cantidad"] * $partida["precio"];
		$partidaiva = ($partida["cantidad"] * $partida["precio"]) * ($cotizacion["tasaiva"] / 100);
		$partidatotal = $partidasubtotal + $partidaiva;
	}

	// if($producto["tipo"]=="E"){
	// 	$tipocotizacion = "E";
	// }

	$pdf->Ln(1);

	$posy = $pdf->GetY();

	$pdf->SetFont('Helvetica', '', 9);

	$extras = "";
	/*if($producto["tipo"]=="K"){
		$kits = mysqli_query($con,"select * from tinventariokits where idinventariopadre = '".$producto["idinventario"]."'");
		while($kit = mysqli_fetch_assoc($kits)){
			$p = mysqli_fetch_assoc(mysqli_query($con,"select * from tinventario where idinventario = '".$kit["idinventario"]."'"));
			$extras .= "\n".mb_convert_encoding("  - ".$p["nombre"]);
		}
	}*/
	$extras = ($partida["es1bordado"] == 1 ? mb_convert_encoding(" , 1 Bordado", "latin1", "UTF-8") : "") . "\n" .
		(($partida["serigrafia1"] > 0 || $partida["serigrafia2"] > 0 || $partida["serigrafia3"] > 0) ? mb_convert_encoding("Serigrafía " . $partida["serigrafia1"] . "  /  " . $partida["serigrafia2"] . "  /  " . $partida["serigrafia3"] . "  /  ", "latin1", "UTF-8") : "") .
		($partida["personalizadonumero"] == 1 ? mb_convert_encoding("Personalizado Número", "latin1", "UTF-8") . "  /  " : "") .
		($partida["personalizadonombre"] == 1 ? mb_convert_encoding("Personalizado Nombre", "latin1", "UTF-8") . "  /  " : "") .
		($partida["bordado1"] == 1 ? mb_convert_encoding("1 Bordado", "latin1", "UTF-8") . "  /  " : "") .
		($partida["bordado2"] == 1 ? mb_convert_encoding("2 Bordados", "latin1", "UTF-8") . "  /  " : "") .
		($partida["bordado3"] == 1 ? mb_convert_encoding("3 Bordados", "latin1", "UTF-8") . "  /  " : "") .
		($partida["bordado4"] == 1 ? mb_convert_encoding("4 Bordados", "latin1", "UTF-8") . "  /  " : "") .
		($partida["bordadoespecial"] == 1 ? mb_convert_encoding("Bordado Especial", "latin1", "UTF-8") . "  /  " : "") .
		($partida["personalizado1linea"] == 1 ? mb_convert_encoding("Personalizado 1 Linea", "latin1", "UTF-8") . "  /  " : "") .
		($partida["personalizado2lineas"] == 1 ? mb_convert_encoding("Personalizado 2 Lineas", "latin1", "UTF-8") . "  /  " : "") .
		($partida["personalizado3lineas"] == 1 ? mb_convert_encoding("Personalizado 3 Lineas", "latin1", "UTF-8") . "  /  " : "") .
		($partida["sxl"] == 1 ? mb_convert_encoding("S-XL", "latin1", "UTF-8") . "  /  " : "") .
		($partida["2xl"] == 1 ? mb_convert_encoding("2XL", "latin1", "UTF-8") . "  /  " : "") .
		($partida["3xl"] == 1 ? mb_convert_encoding("3XL", "latin1", "UTF-8") . "  /  " : "") .
		($partida["observaciones"] != "" ? mb_convert_encoding("Observaciones: " . $partida["observaciones"], "latin1", "UTF-8") : "");

	$extras = rtrim($extras, " /");

	$pdf->MultiCell(106, 5, mb_convert_encoding($nombre, "latin1", "UTF-8") . $extras, 0, 'L', ($num % 2 == 0) ? true : false);

	$posy2 = $pdf->GetY();

	$pdf->SetXY(116, $posy);

	$pdf->Cell(20, 5, mb_convert_encoding($partida["cantidad"], "latin1", "UTF-8"), 0, 0, 'C', ($num % 2 == 0) ? true : false);
	$pdf->Cell(35, 5, "$" . number_format($preciounitario, 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);
	$pdf->Cell(35, 5, "$" . number_format($partidasubtotal, 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);

	// if($cotizacion["subtotalizar"]==1){
	// 	$pdf->Cell(35,5,"$".number_format($partidasubtotal,2),0,0,'R');
	// }else{
	// 	$pdf->MultiCell(35,5,"$".number_format($partidasubtotal,2),0,'R');
	// 	if($pdf->GetY() > $posy2){
	// 		$posy2 = $pdf->GetY();
	// 	}
	// }

	if ($num % 2 == 0 && $posy2 > $posy + 5) {
		$pdf->Rect(116, ($posy2 - $posy) + $posy - 5, 90, 5, 'F');
	}

	$pdf->SetY($posy2);
	$num++;
	//$pdf->Line(10,$posy2 + 1,206,$posy2 + 1);
}

if ($cotizacion["total"] > 0 && $cotizacion["subtotalizar"] == 1) {
	$pdf->Ln(15);
	$pdf->SetFont('Helvetica', '', 9);
	if ($cotizacion["subtotal"] > 0 && $cotizacion["iva"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("Subtotal $" . number_format($cotizacion["subtotal"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
	if ($cotizacion["iva"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("IVA $" . number_format($cotizacion["iva"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
	if ($cotizacion["total"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("Total $" . number_format($cotizacion["total"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
}

$pdf->Ln(15);
$pdf->SetFont('Helvetica', '', 10);

$posy = $pdf->GetY();
$i = 1;
$altura = 7;
while ($i <= 5) {
	if ($cotizacion["linea" . $i] != "") {
		$altura += 7;
	}
	$i++;
}
if ($posy > (265 - $altura)) {
	$pdf->AddPage();
} else {
	$pdf->SetY(265 - $altura);
}

// vigencia 
$pdf->Cell(0, 0, mb_convert_encoding("Vigencia: " . fecha_formateada_largo(explode(" ", $cotizacion["fechavigencia"])[0]), "latin1", "UTF-8"), 0, 0, 'L');
$pdf->Ln(5);
$i = 1;
while ($i <= 5) {
	if ($cotizacion["linea" . $i] != "") {
		$pdf->Cell(0, 0, mb_convert_encoding("* " . $cotizacion["linea" . $i], "latin1", "UTF-8"), 0, 0, 'L');
		$pdf->Ln(5);
	}
	$i++;
}

$nombre = $cotizacion["cliente"] . " Cotizacion #" . $cotizacion["idcotizacion"];

$nombre = str_replace(" ", "_", $nombre);

$pdf->Output("I", $nombre . ".pdf");
