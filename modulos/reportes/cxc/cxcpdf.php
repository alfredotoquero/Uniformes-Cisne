<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Reportes.php");

require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf/fpdf.php");
require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf-easytable/exfpdf.php");
require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf-easytable/easyTable.php");

$_POST["slcCliente"] = $_GET["slcCliente"];
$_POST["slcSucursales"] = $_GET["slcSucursales"];

$claseReportes = new Reportes();
$sucursales = $claseReportes->obtenerCXC($_POST);

$fecha = date("Y-m-d");

$pdf = new exFPDF();
$pdf->AddPage('O');
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/membreteuniformes.jpg", 0, 0, 220, 280);
$pdf->SetY(45);
$pdf->Cell(0, 0, mb_convert_encoding(fecha_formateada_largo(explode(" ", $fecha)[0]), "latin1", "UTF-8"), 0, 0, 'R');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Ln(10);
$pdf->Cell(50, 4, mb_convert_encoding("Reporte de Cuentas por Cobrar:", "latin1", "UTF-8"), 'B', 0, 'L');

foreach ($sucursales["sucursales"] as $sucursal) {
	if (strlen($sucursal["idpedidos"]) == 0) {
		continue;
	}

	$pdf->Ln(10);
	$sucursal_nombre = new easyTable($pdf, 1);
	$sucursal_nombre->easyCell(mb_convert_encoding($sucursal["nombre"], "latin1", "UTF-8"), 'font-family:Helvetica; font-size:10; font-style:B; font-color:#000000;');
	$sucursal_nombre->printRow(false);
	$sucursal_nombre->endTable(4);

	$tabla = new easyTable($pdf, '{27, 12, 83, 27, 27, 27, 32, 37}', 'align:L; ');
	$tabla->rowStyle('font-family:Helvetica; font-size:10; font-style:Bold; font-color:#000000;');
	$tabla->easyCell(mb_convert_encoding('Fecha', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('#', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Cliente', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Importe', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Abonado', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Restante', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Status Pedido', "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding('Status Producción', "latin1", "UTF-8"));
	$tabla->printRow();

	$total = 0;
	$abonado = 0;
	$restante = 0;
	$fechas = explode(",", $sucursal["fechas"]);

	$idpedidos = explode("*,", $sucursal["idpedidos"]);
	$clientes = explode("*,", $sucursal["clientes"]);
	$totales = explode("*,", $sucursal["totales"]);
	$abonados = explode("*,", $sucursal["abonados"]);
	$statuspedidos = explode("*,", $sucursal["statuspedidos"]);
	$statusproduccion = explode("*,", $sucursal["statusproduccion"]);
	foreach ($idpedidos as $i => $idpedido) {
		$tabla->rowStyle('font-family:Helvetica; font-size:10; font-style:Normal; font-color:#000000;');
		$tabla->easyCell(mb_convert_encoding(fecha_formateada(explode(" ", $fechas[$i])[0]), "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding($idpedido, "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding($clientes[$i], "latin1", "UTF-8"));
		$total += $totales[$i];
		$abonado += $abonados[$i];
		$restante += $totales[$i] - $abonados[$i];
		$tabla->easyCell(mb_convert_encoding("$" . number_format($totales[$i], 2), "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding("$" . number_format($abonados[$i], 2), "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding("$" . number_format($totales[$i] - $abonados[$i], 2), "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding((($statuspedidos[$i] == "A") ? "Activo" : (($statuspedidos[$i] == "E") ? "Entregado" : "Cancelado")), "latin1", "UTF-8"));
		$tabla->easyCell(mb_convert_encoding((($statusproduccion[$i] == "P") ? "En Proceso" : (($statusproduccion[$i] == "T") ? "Terminado" : "No iniciado")), "latin1", "UTF-8"));
		$tabla->printRow();
	}
	$pdf->Ln(8);
	$tabla->easyCell('Total', 'font-style:B;');
	$tabla->easyCell(' ', 'colspan:2;');
	$tabla->easyCell(mb_convert_encoding("$" . number_format($total, 2), "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding("$" . number_format($abonado, 2), "latin1", "UTF-8"));
	$tabla->easyCell(mb_convert_encoding("$" . number_format($restante, 2), "latin1", "UTF-8"));
	$tabla->easyCell(' ', 'colspan:2;');
	$tabla->printRow();

	$tabla->endTable(2);
}


$pdf->Ln(15);
$nombre = "Reporte Cortes " . $fecha;

$nombre = str_replace(" ", "_", $nombre);

$pdf->Output("I", $nombre . ".pdf");

// foreach ($sucursales["sucursales"] as $sucursal) {
// 	if (strlen($sucursal["idpedidos"]) == 0) {
// 		continue;
// 	}

// 	$pdf->SetFont('Helvetica', 'B', 9);
// 	$pdf->Ln(10);
// 	$pdf->Cell(100, 5, mb_convert_encoding($sucursal["nombre"], "latin1", "UTF-8"), '', 0, 'L');

// 	$pdf->Ln(8);
// 	$pdf->SetFont('Helvetica', 'B', 9);
// 	$pdf->Cell(25, 5, mb_convert_encoding('Fecha', "latin1", "UTF-8"), '', 0, 'L');
// 	$pdf->Cell(10, 5, mb_convert_encoding('#', "latin1", "UTF-8"), '', 0, 'L');
// 	$pdf->Cell(80, 5, mb_convert_encoding('Cliente', "latin1", "UTF-8"), '', 0, 'L');
// 	$pdf->Cell(25, 5, mb_convert_encoding('Importe', "latin1", "UTF-8"), '', 0, 'R');
// 	$pdf->Cell(25, 5, mb_convert_encoding('Abonado', "latin1", "UTF-8"), '', 0, 'R');
// 	$pdf->Cell(25, 5, mb_convert_encoding('Restante', "latin1", "UTF-8"), '', 0, 'R');
// 	$pdf->Cell(30, 5, mb_convert_encoding('Status Pedido', "latin1", "UTF-8"), '', 0, 'R');
// 	$pdf->Cell(35, 5, mb_convert_encoding('Status Producción', "latin1", "UTF-8"), '', 0, 'R');
// 	$pdf->Ln(5);

// 	$Y = $pdf->getY();

// 	$total = 0;
// 	$fechas = explode(",", $sucursal["fechas"]);
// 	$idpedidos = explode(",", $sucursal["idpedidos"]);
// 	$clientes = explode(",", $sucursal["clientes"]);
// 	$totales = explode(",", $sucursal["totales"]);
// 	$abonados = explode(",", $sucursal["abonados"]);
// 	$statuspedidos = explode(",", $sucursal["statuspedidos"]);
// 	$statusproduccion = explode(",", $sucursal["statusproduccion"]);
// 	foreach ($idpedidos as $i => $idpedido) {
// 		$pdf->Ln(5);
// 		$pdf->setY($Y);
// 		$pdf->SetFont('Helvetica', '', 9);

// 		$pdf->Cell(25, 5, mb_convert_encoding(fecha_formateada(explode(" ", $fechas[$i])[0]), "latin1", "UTF-8"), 0, 'L');
// 		$pdf->Cell(10, 5, mb_convert_encoding($idpedido, "latin1", "UTF-8"), 0, 'L');
// 		$pdf->MultiCell(80, 5, mb_convert_encoding($clientes[$i], "latin1", "UTF-8"), 0, 'L');

// 		$H = $pdf->GetY();
// 		if ($H > $Y) {
// 			$height = $H - $Y;
// 		} else {
// 			$Y = $H - 10;
// 		}

// 		$total += $totales[$i];

// 		$pdf->SetXY(136, $Y);
// 		$pdf->Cell(25, $height, mb_convert_encoding("$" . number_format($totales[$i], 2), "latin1", "UTF-8"), 0, 'R');
// 		$pdf->Cell(25, $height, mb_convert_encoding("$" . number_format($abonados[$i], 2), "latin1", "UTF-8"), 0, 'R');
// 		$pdf->Cell(25, $height, mb_convert_encoding("$" . number_format($totales[$i] - $abonados[$i], 2), "latin1", "UTF-8"), 0, 'R');
// 		$pdf->Cell(30, $height, mb_convert_encoding((($statuspedidos[$i] == "A") ? "Activo" : (($statuspedidos[$i] == "E") ? "Entregado" : "Cancelado")), "latin1", "UTF-8"), 0, 'R');
// 		$pdf->Cell(35, $height, mb_convert_encoding((($statusproduccion[$i] == "P") ? "En Proceso" : (($statusproduccion[$i] == "T") ? "Terminado" : "No iniciado")), "latin1", "UTF-8"), 0, 'R');

// 		$Y = $H;
// 	}
// 	$pdf->Ln(8);
// 	$pdf->SetFont('Helvetica', 'B', 9);
// 	$pdf->Cell(127, 5, mb_convert_encoding('Total:', "latin1", "UTF-8"), '', 0, 'L');
// 	$pdf->Cell(15, 5, mb_convert_encoding("$" . number_format($total, 2), "latin1", "UTF-8"), '', 0, 'R');
// }


// $pdf->Ln(15);
// $pdf->SetFont('Helvetica', '', 10);

// $nombre = "Reporte Cortes " . $fecha;

// $nombre = str_replace(" ", "_", $nombre);

// $pdf->Output("I", $nombre . ".pdf");
