<?
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Cotizaciones.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");

require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf/fpdf.php");

$claseCotizaciones = new Cotizaciones();
$clasePedidos = new Pedidos();
$claseSucursales = new Sucursales();

$_POST["idpedido"] = $_GET["idpedido"];

$pedido = $clasePedidos->obtenerPedido($_POST)["pedido"];

$_POST["idcotizacion"] = $pedido["idcotizacion"];
$cotizacion = $claseCotizaciones->obtenerCotizacion($_POST)["cotizacion"];


// Creación del objeto de la clase heredada
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetAutoPageBreak(false);

$formato = $claseSucursales->obtenerSucursal(array("idsucursal" => $pedido["idsucursal"]))["sucursal"]["formato"];
if(!is_null($formato) && file_exists($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato)){
	$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato, 0, 0, 220, 280);
}else{
	$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/membreteuniformes.jpg", 0, 0, 220, 280);
}

$pdf->SetY(45);
$pdf->Cell(0, 0, mb_convert_encoding(fecha_formateada_largo(explode(" ", $pedido["fecha"])[0]), "latin1", "UTF-8"), 0, 0, 'R');

$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(10);
$pdf->Cell(20, 4, mb_convert_encoding("Pedido: " . $pedido["idpedido"], "latin1", "UTF-8"), 'B', 0, 'L');

$pdf->SetFont('Helvetica', '', 9);
$pdf->Ln(10);
$pdf->Cell(0, 0, mb_convert_encoding('Attn: --- ' . (($pedido["cliente"] != "") ?  (($pedido["contacto"] != "") ? $pedido["contacto"] . " / " : "") . $pedido["cliente"] : "Público en general") . ' ---', "latin1", "UTF-8"), 0, 0, 'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(0, 0, mb_convert_encoding('Atendiendo su petición extendemos la presente para poner a su consideración nuestros servicios.', "latin1", "UTF-8"), 0, 0, 'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(106, 5, mb_convert_encoding('Concepto', "latin1", "UTF-8"), '', 0, 'L');
$pdf->Cell(20, 5, mb_convert_encoding('Cantidad', "latin1", "UTF-8"), '', 0, 'C');
$pdf->Cell(35, 5, mb_convert_encoding('P. Unitario', "latin1", "UTF-8"), '', 0, 'R');
$pdf->Cell(35, 5, mb_convert_encoding('Total', "latin1", "UTF-8"), '', 0, 'R');
$pdf->Ln(5);

$num = 1;
$partidas = $clasePedidos->obtenerPartidasCotizacion($_POST);

// $partidas = $clasePedidos->obtenerPartidasCotizacion2($_POST)["partidas"];
foreach ($partidas["partidas"] as $partida) {

	$producto = $partida["producto"];

	$cadenaespecificaciones = "";
	$cadenaespecificaciones .= (($partida["serigrafia1"] > 0 || $partida["serigrafia2"] > 0 || $partida["serigrafia3"] > 0) ? "Serigrafía " . $partida["serigrafia1"] . " / " . $partida["serigrafia2"] . " / " . $partida["serigrafia3"] : "");
	$cadenaespecificaciones .= ($partida["personalizadonumero"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado Número" : "");
	$cadenaespecificaciones .= ($partida["personalizadonombre"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado Nombre" : "");
	$cadenaespecificaciones .= ($partida["bordado1"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "1 Bordado" : "");
	$cadenaespecificaciones .= ($partida["bordado2"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "2 Bordados" : "");
	$cadenaespecificaciones .= ($partida["bordado3"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "3 Bordados" : "");
	$cadenaespecificaciones .= ($partida["bordado4"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "4 Bordados" : "");
	$cadenaespecificaciones .= ($partida["bordadoespecial"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Bordado Especial" : "");
	$cadenaespecificaciones .= ($partida["personalizado1linea"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 1 Linea" : "");
	$cadenaespecificaciones .= ($partida["personalizado2lineas"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 2 Lineas" : "");
	$cadenaespecificaciones .= ($partida["personalizado3lineas"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "Personalizado 3 Lineas" : "");
	$cadenaespecificaciones .= ($partida["sxl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "S-XL" : "");
	$cadenaespecificaciones .= ($partida["2xl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "2XL" : "");
	$cadenaespecificaciones .= ($partida["3xl"] == 1 ? (($cadenaespecificaciones != "") ? ", " : "") . "3XL" : "");
	$cadenaespecificaciones .= ($partida["observaciones"] != "" ? (($cadenaespecificaciones != "") ? ", " : "") . "Observaciones: " . $partida["observaciones"] : "");
	$cadenaespecificaciones = ($cadenaespecificaciones != "") ? "\n" . mb_convert_encoding($cadenaespecificaciones, "latin1", "UTF-8") : "";

	// se determina la cantidad por partida, ya que existe la posibilidad de que no esté desglosada
	$cantidad = $partida["cantidad"];
	$extras = "";

	// $cantidad = 0;
	$desgloses = explode(";", $partida["desgloses"]);
	foreach ($desgloses as $desglose) {
		$desglose = explode(" : ", $desglose);
		$desglose = array("color" => $desglose[0], "tallas" => $desglose[1]);

		$color = $desglose["color"];

		$extras .= ("\n" . mb_convert_encoding($color, "latin1", "UTF-8")) . " / " . $desglose["tallas"];

		// $tallas = explode(" / ",$desglose["tallas"]);
		// foreach ($tallas as $talla) {
		// 	$talla = explode(" - ",$talla);
		// 	$talla = array("talla"=>$talla[0],"cantidad"=>$talla[1]);

		// 	$extras .= " / " . $talla["cantidad"] . " - " . mb_convert_encoding($talla["talla"]);

		// 	// $cantidad += $talla["cantidad"];
		// }
	}

	$extras = rtrim($extras, "\n /");

	// $desgloses = explode(";",$partida["desgloses"]);

	// $color = $partida["color"];
	// $cantidad = 0;
	// // $talla = $partida["talla"];
	// $extras .= ("\n" . mb_convert_encoding($color));
	// foreach ($desgloses as $desglose) {
	// 	// $extras .= ("\n" . mb_convert_encoding($color));

	// 	$datos = explode(",",$desglose);
	// 	$talla = $datos[0];
	// 	$cant = $datos[1];

	// 	$extras .= " / " . $cant . " - " . mb_convert_encoding($talla);

	// 	$cantidad += $cant;
	// }

	$pdf->Ln(1);

	$posy = $pdf->GetY();

	$pdf->SetFont('Helvetica', '', 9);

	$pdf->MultiCell(106, 5, mb_convert_encoding($producto, "latin1", "UTF-8") . $cadenaespecificaciones . $extras, 0, 'L', ($num % 2 == 0) ? true : false);

	$posy2 = $pdf->GetY();

	$pdf->SetXY(116, $posy);

	$pdf->Cell(20, 5, mb_convert_encoding($cantidad, "latin1", "UTF-8"), 0, 0, 'C', ($num % 2 == 0) ? true : false);
	$pdf->Cell(35, 5, "$" . number_format((($pedido["incluyeiva"] == 1) ? $partida["precio"] / (1 + ($pedido["tasaiva"] / 100)) : $partida["precio"]), 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);
	$pdf->Cell(35, 5, "$" . number_format((($pedido["incluyeiva"] == 1) ? $partida["precio"] / (1 + ($pedido["tasaiva"] / 100)) : $partida["precio"]) * $cantidad, 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);

	if ($num % 2 == 0 && $posy2 > $posy + 5) {
		$pdf->Rect(116, $posy + 5, 90, $posy2 - ($posy + 5), 'F');
	}

	if ($posy2 > $pdf->GetPageHeight() - 10) {
		$pdf->SetFillColor(255, 255, 255);
		$pdf->Rect(10, $posy, 196, $pdf->GetPageHeight() - $posy, 'F');
		$pdf->SetFillColor(240, 240, 240);
		$pdf->AddPage();
		$pdf->SetY(10);

		$posy = $pdf->GetY();

		$pdf->SetFont('Helvetica', '', 9);

		$pdf->MultiCell(106, 5, mb_convert_encoding($producto, "latin1", "UTF-8") . $cadenaespecificaciones . $extras, 0, 'L', ($num % 2 == 0) ? true : false);

		$posy2 = $pdf->GetY();

		$pdf->SetXY(116, $posy);

		$pdf->Cell(20, 5, mb_convert_encoding($cantidad, "latin1", "UTF-8"), 0, 0, 'C', ($num % 2 == 0) ? true : false);
		$pdf->Cell(35, 5, "$" . number_format((($pedido["incluyeiva"] == 1) ? $partida["precio"] / (1 + ($pedido["tasaiva"] / 100)) : $partida["precio"]), 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);
		$pdf->Cell(35, 5, "$" . number_format((($pedido["incluyeiva"] == 1) ? $partida["precio"] / (1 + ($pedido["tasaiva"] / 100)) : $partida["precio"]) * $cantidad, 2), 0, 0, 'R', ($num % 2 == 0) ? true : false);

		if ($num % 2 == 0 && $posy2 > $posy + 5) {
			$pdf->Rect(116, $posy + 5, 90, $posy2 - ($posy + 5), 'F');
		}
	}

	$pdf->SetY($posy2);
	$num++;
	//$pdf->Line(10,$posy2 + 1,206,$posy2 + 1);
}

$pdf->SetAutoPageBreak(true);

if ($pedido["total"] > 0) {
	$pdf->Ln(15);
	$pdf->SetFont('Helvetica', '', 9);
	if ($pedido["subtotal"] > 0 && $pedido["iva"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("Subtotal $" . number_format($pedido["subtotal"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
	if ($pedido["iva"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("IVA $" . number_format($pedido["iva"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
	if ($pedido["total"] > 0) {
		$pdf->Cell(0, 0, mb_convert_encoding("Total $" . number_format($pedido["total"], 2), "latin1", "UTF-8"), 0, 0, 'R');
		$pdf->Ln(5);
	}
}

$pdf->Ln(15);
$pdf->SetFont('Helvetica', '', 10);

if ($posy > 252) {
	$pdf->AddPage();
} else {
	$pdf->SetY(252);
}

$pdf->Cell(0, 0, mb_convert_encoding("* " . $cotizacion["linea1"], "latin1", "UTF-8"), 0, 0, 'L');

$nombre = $pedido["cliente"] . " Pedido #" . $pedido["idpedido"];

$nombre = str_replace(" ", "_", $nombre);

$pdf->Output("I", $nombre . ".pdf");
