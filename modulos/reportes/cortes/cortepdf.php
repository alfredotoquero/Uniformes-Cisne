<?

function fecha_formateada($fecha){
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia." de ";

	switch($mes){
		case "01": $fecha.= "Enero"; break;
		case "02": $fecha.= "Febrero"; break;
		case "03": $fecha.= "Marzo"; break;
		case "04": $fecha.= "Abril"; break;
		case "05": $fecha.= "Mayo"; break;
		case "06": $fecha.= "Junio"; break;
		case "07": $fecha.= "Julio"; break;
		case "08": $fecha.= "Agosto"; break;
		case "09": $fecha.= "Septiembre"; break;
		case "10": $fecha.= "Octubre"; break;
		case "11": $fecha.= "Noviembre"; break;
		case "12": $fecha.= "Diciembre"; break;
	}

	$fecha .= " de ".$ano;
	return $fecha;
}

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Reportes.php");

require($_SERVER["DOCUMENT_ROOT"]."/assets/plugins/fpdf/fpdf.php");

$_POST["slcVendedor"] = $_GET["idvendedor"];
$_POST["slcSucursales"] = $_GET["slcSucursales"];
$_POST["txtFechaInicial"] = $_GET["txtFechaInicial"];
$_POST["txtFechaFinal"] = $_GET["txtFechaFinal"];

$claseReportes = new Reportes();
$cortes = $claseReportes->obtenerCortes($_POST);

$fecha = date("Y-m-d");

// Creación del objeto de la clase heredada
$pdf = new FPDF('P','mm','Letter');
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(240,240,240);
// $pdf->SetAutoPageBreak(false);
// $pdf->SetAutoPageBreak(true);

$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/membreteuniformes.jpg",0,0,220,280);

$pdf->SetY(45);
$pdf->Cell(0,0,utf8_decode(fecha_formateada(explode(" ",$fecha)[0])),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(31,4,utf8_decode("Reporte de Cortes:"),'B',0,'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(40,5,utf8_decode('Sucursal'),'',0,'L');
$pdf->Cell(25,5,utf8_decode('Vendedor'),'',0,'L');
$pdf->Cell(10,5,utf8_decode('Folio'),'',0,'L');
$pdf->Cell(40,5,utf8_decode('Fecha'),'',0,'L');
$pdf->Cell(30,5,utf8_decode('Fondo Inicial'),'',0,'R');
// $pdf->Cell(30,5,utf8_decode('Ventas'),'',0,'R');
$pdf->Cell(25,5,utf8_decode('Devoluciones'),'',0,'R');
$pdf->Cell(25,5,utf8_decode('Total Ventas'),'',0,'R');
// $pdf->Cell(30,5,utf8_decode('Status'),'',0,'R');
$pdf->Ln(5);

// $pdf->Cell(200,5,utf8_decode('Wheres: ' . $where),'',0,'R');

$totalventas = 0;
$totaldevoluciones = 0;
foreach ($cortes["cortes"] as $corte) {
	
	if ($corte["status"]=="A") {
		$ventas = $corte["totalventas"];
        $devoluciones = $corte["totaldevoluciones"];
	}else {
		$ventas = $corte["ventas"];
        $devoluciones = $corte["devoluciones"];
	}

	$pdf->Ln(5);
	$posy = $pdf->GetY();
	$pdf->SetFont('Helvetica','',9);

	$pdf->MultiCell(40,5,utf8_decode($corte["sucursal"]),0,'L');
	$pdf->SetXY(50,$pdf->GetY()-5);
	$pdf->MultiCell(25,5,utf8_decode($corte["vendedor"]),0,'L');
	$pdf->SetXY(75,$pdf->GetY()-5);
	$pdf->MultiCell(10,5,utf8_decode($corte["folio"]),0,'L');
	$pdf->SetXY(85,$pdf->GetY()-5);
	$pdf->MultiCell(40,5,utf8_decode(fecha_formateada(substr($corte["fechainicial"],0,10))),0,'L');
	$pdf->SetXY(125,$pdf->GetY()-5);
	$pdf->MultiCell(30,5,utf8_decode("$ " . number_format($corte["fondoinicial"],2)),0,'R');
	$pdf->SetXY(155,$pdf->GetY()-5);
	$pdf->MultiCell(25,5,utf8_decode("$ " . number_format($devoluciones,2)),0,'R');
	$pdf->SetXY(180,$pdf->GetY()-5);
	$pdf->MultiCell(25,5,utf8_decode("$ " . number_format($ventas,2)),0,'R');

    $totalventas += $ventas;
    $totaldevoluciones += $devoluciones;
}

$pdf->Ln(15);
$pdf->SetFont('Helvetica','B',9);
$pdf->SetXY(10,$pdf->GetY()-5);
$pdf->MultiCell(25,5,utf8_decode("Totales"),0,'L');
$pdf->SetXY(155,$pdf->GetY()-5);
$pdf->MultiCell(25,5,utf8_decode("$".number_format($totaldevoluciones,2)),0,'R');
$pdf->SetXY(180,$pdf->GetY()-5);
$pdf->MultiCell(25,5,utf8_decode("$".number_format($totalventas,2)),0,'R');

$pdf->Ln(15);
$pdf->SetFont('Helvetica','',10);

$nombre = "Reporte Cortes ".$fecha;

$nombre = str_replace(" ","_",$nombre);

$pdf->Output("I",$nombre.".pdf");

?>