<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

require($_SERVER["DOCUMENT_ROOT"]."/assets/plugins/fpdf/fpdf.php");

// include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from vmovimientos where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));

$mialmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["4dm1npl4y3r4sc1sn3usr"]."')"))["idalmacen"];

// Creación del objeto de la clase heredada
$pdf = new FPDF('P','mm','Letter');
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(182,182,182);

$pdf->Image("../../assets/images/formatoplayeras.jpg",0,0,220,280);

$pdf->SetY(45);
$pdf->Cell(0,0,utf8_decode(fecha_formateada_largo(explode(" ",$movimiento["fecha"])[0])),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(25,4,utf8_decode("Movimiento #" . $movimiento["idmovimientoinventario"]),'',0,'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,0,utf8_decode("Tipo de movimiento: " . $movimiento["movimiento"]),0,0,'L');

if ($movimiento["idtipomovimiento"]=="2") {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("De: " . $movimiento["almacen"]),0,0,'L');
} else if ($movimiento["idtipomovimiento"]=="1") {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("A: " . $movimiento["almacen"]),0,0,'L');
}else {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("De: " . $movimiento["almacen"]),0,0,'L');
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("A: " . $movimiento["almacensecundario"]),0,0,'L');
}

$pdf->Ln(8);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(20,5,utf8_decode('Cantidad'),'',0,'C');
$pdf->Cell(106,5,utf8_decode('Producto'),'',0,'L');
$pdf->Cell(20,5,utf8_decode('Talla'),'',0,'R');
$pdf->Cell(50,5,utf8_decode('Color'),'',0,'R');

$partidas = mysqli_query($con,"select * from vmovimientoproductos where idmovimientoinventario = '".$movimiento["idmovimientoinventario"]."' order by producto,color,posicion_talla");
while($partida = mysqli_fetch_assoc($partidas)){
	$pdf->Ln(5);

	$pdf->SetFont('Helvetica','',9);
	
	$posy2 = $pdf->GetY();
	$pdf->Line(10,$posy2 + 1,206,$posy2 + 1);
	
	$pdf->Ln(2);
	
	$pdf->Cell(20,5,utf8_decode($partida["cantidad"]),0,0,'C');
	$pdf->Cell(106,5,$partida["producto"],0,0,'L');
	$pdf->Cell(20,5,$partida["talla"],0,0,'R');
	$pdf->Cell(50,5,$partida["color"],0,0,'R');

	$cantidadtotal += $partida["cantidad"];
}

$pdf->Ln(5);
$posy2 = $pdf->GetY();
$pdf->Line(10,$posy2 + 1,206,$posy2 + 1);
$pdf->Ln(5);
$pdf->Cell(0,5,utf8_decode("Total de productos: ".$cantidadtotal),0,0,'L');

$pdf->Ln(15);
$pdf->SetFont('Helvetica','',10);

// $pdf->SetY(265-15);

// notas 
$pdf->Cell(0,0,utf8_decode("Notas: ".$movimiento["notas"]),0,0,'L');


$nombre = "asd";

$pdf->Output("I",$nombre.".pdf");
?>