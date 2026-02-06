<?
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 600);

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Compras.php");

$_POST["idcompra"] = $_GET["idcompra"];
$_POST["idrecepcion"] = $_GET["idrecepcion"];
$claseCompras = new Compras();
$recepcion = $claseCompras->obtenerRecepciones($_POST)["recepciones"][0];

$lenUsuario = strlen("Usuario: " . $recepcion["usuario"]);
$lenTitulo = strlen("Recepción - Compra: #" . $recepcion["idcompra"]);

function fecha_formateada($fecha)
{
    $fecha = explode("-", $fecha);
    $dia = $fecha[2];
    $mes = $fecha[1];
    $ano = $fecha[0];

    $fecha = $dia . " de ";

    switch ($mes) {
        case "01":
            $fecha .= "Enero";
            break;
        case "02":
            $fecha .= "Febrero";
            break;
        case "03":
            $fecha .= "Marzo";
            break;
        case "04":
            $fecha .= "Abril";
            break;
        case "05":
            $fecha .= "Mayo";
            break;
        case "06":
            $fecha .= "Junio";
            break;
        case "07":
            $fecha .= "Julio";
            break;
        case "08":
            $fecha .= "Agosto";
            break;
        case "09":
            $fecha .= "Septiembre";
            break;
        case "10":
            $fecha .= "Octubre";
            break;
        case "11":
            $fecha .= "Noviembre";
            break;
        case "12":
            $fecha .= "Diciembre";
            break;
    }

    $fecha .= " de " . $ano;
    return $fecha;
}

require($_SERVER["DOCUMENT_ROOT"] . '/assets/plugins/fpdf/fpdf.php');

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(182, 182, 182);

$anchoHoja = $pdf->GetPageWidth() - 20;
$lenEspacio = $anchoHoja - ceil($lenTitulo * 1.7) - ceil($lenUsuario * 1.7);
$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/formatouniformes.jpg", 0, 0, 220, 280);

$pdf->SetY(45);
$pdf->Cell(0, 0, mb_convert_encoding(fecha_formateada(explode(" ", $recepcion["registro"])[0]), "latin1", "UTF-8"), 0, 0, 'R');

$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(10);
$pdf->Cell(41, 4, mb_convert_encoding("Recepción - Compra: #" . $recepcion["idcompra"], "latin1", "UTF-8"), 'B', 0, 'L');
$pdf->Cell($lenEspacio, 4, '', '', 0, 'L');
$pdf->Cell(0, 4, mb_convert_encoding("Usuario: " . $recepcion["usuario"], "latin1", "UTF-8"), 'B', 0, 'R');



$pdf->Ln(8);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell($anchoHoja * .2, 5, mb_convert_encoding('Cantidad', "latin1", "UTF-8"), '', 0, 'L');
$pdf->Cell($anchoHoja * .4, 5, mb_convert_encoding('Producto', "latin1", "UTF-8"), '', 0, 'L');
$pdf->Cell($anchoHoja * .2, 5, mb_convert_encoding('Talla', "latin1", "UTF-8"), '', 0, 'C');
$pdf->Cell($anchoHoja * .2, 5, mb_convert_encoding('Color', "latin1", "UTF-8"), '', 0, 'C');

$pdf->Ln(5);

$totalproductos = 0;
$recepcionProductos = $claseCompras->obtenerRecepcionProductos($_POST);
foreach ($recepcionProductos["productos"] as $producto) {
    $pdf->Ln(2);

    $posy = $pdf->GetY();

    $pdf->SetFont('Helvetica', '', 9);

    $pdf->Cell($anchoHoja * .2, 5, $producto["cantidad"], 0, 0, 'L');

    $pdf->MultiCell($anchoHoja * .2, 5, mb_convert_encoding($producto["producto"], "latin1", "UTF-8"), 0, 'L');

    $posy = $pdf->GetY() - 5;

    $pdf->SetXY(($anchoHoja * .2) + ($anchoHoja * .4) + 10, $posy);

    $pdf->Cell(40, 5, $producto["talla"], 0, 0, 'C');

    $pdf->Cell(40, 5, mb_convert_encoding($producto["color"], "latin1", "UTF-8"), 0, 0, 'C');

    $pdf->Ln(4);


    $pdf->SetFont('Helvetica', '', 9);

    $posy2 = $pdf->GetY();

    $totalproductos += $producto["cantidad"];
}

$pdf->Ln(10);

$pdf->Cell(0, 5, "Total de productos: " . $totalproductos, 0, 0, 'L');

$nombre = "RecepcionCompra#" . $_GET["idcompra"];

$pdf->Output('I', $nombre . ".pdf");
