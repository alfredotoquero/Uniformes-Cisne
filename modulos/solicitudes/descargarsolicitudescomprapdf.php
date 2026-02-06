<?
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 600);

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Proveedores.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Solicitudes.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Productos.php");

$claseProveedores = new Proveedores();
$claseSolicitudes = new Solicitudes();
$claseProductos = new Productos();

// function fecha_formateada($fecha){
// 	$fecha = explode("-",$fecha);
// 	$dia = $fecha[2];
// 	$mes = $fecha[1];
// 	$ano = $fecha[0];

// 	$fecha = $dia." de ";

// 	switch($mes){
// 		case "01": $fecha.= "Enero"; break;
// 		case "02": $fecha.= "Febrero"; break;
// 		case "03": $fecha.= "Marzo"; break;
// 		case "04": $fecha.= "Abril"; break;
// 		case "05": $fecha.= "Mayo"; break;
// 		case "06": $fecha.= "Junio"; break;
// 		case "07": $fecha.= "Julio"; break;
// 		case "08": $fecha.= "Agosto"; break;
// 		case "09": $fecha.= "Septiembre"; break;
// 		case "10": $fecha.= "Octubre"; break;
// 		case "11": $fecha.= "Noviembre"; break;
// 		case "12": $fecha.= "Diciembre"; break;
// 	}

// 	$fecha .= " de ".$ano;
// 	return $fecha;
// }

include($_SERVER["DOCUMENT_ROOT"]."/assets/plugins/fpdf/fpdf.php");

class PDF extends FPDF{

	public $tablewidths;
	public $footerset;

	function _beginpage($orientation, $size, $rotation) {
		$this->page++;
		if(!isset($this->pages[$this->page])) // solves the problem of overwriting a page if it already exists
			$this->pages[$this->page] = '';
		$this->state = 2;
		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
		$this->FontFamily = '';
		// Check page size and orientation
		if($orientation=='')
			$orientation = $this->DefOrientation;
		else
			$orientation = strtoupper($orientation[0]);
		if($size=='')
			$size = $this->DefPageSize;
		else
			$size = $this->_getpagesize($size);
		if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
		{
			// New size or orientation
			if($orientation=='P')
			{
				$this->w = $size[0];
				$this->h = $size[1];
			}
			else
			{
				$this->w = $size[1];
				$this->h = $size[0];
			}
			$this->wPt = $this->w*$this->k;
			$this->hPt = $this->h*$this->k;
			$this->PageBreakTrigger = $this->h-$this->bMargin;
			$this->CurOrientation = $orientation;
			$this->CurPageSize = $size;
		}
		if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
			$this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
		if($rotation!=0)
		{
			if($rotation%90!=0)
				$this->Error('Incorrect rotation value: '.$rotation);
			$this->CurRotation = $rotation;
			$this->PageInfo[$this->page]['rotation'] = $rotation;
		}
	}

	function morepagestable($datas, $alineaciones, $lineheight=5) {
		// some things to set and 'remember'
		$l = $this->lMargin;
		$startheight = $h = $this->GetY();
		$startpage = $currpage = $maxpage = $this->page;

		// calculate the whole width
		$fullwidth = 0;
		foreach($this->tablewidths AS $width) {
			$fullwidth += $width;
		}

		// Now let's start to write the table
		$numalineacion = 0;
		foreach($datas AS $row => $data) {
			$this->page = $currpage;
			// write the horizontal borders
			$this->Ln(5);
			// write the content and remember the height of the highest col
			foreach($data AS $col => $txt) {
				$this->page = $currpage;
				$this->SetXY($l,$h+1);
				$this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$alineaciones[$numalineacion][$col]);
				$l += $this->tablewidths[$col];

				if(!isset($tmpheight[$row.'-'.$this->page]))
					$tmpheight[$row.'-'.$this->page] = 0;
				if($tmpheight[$row.'-'.$this->page] < $this->GetY()) {
					$tmpheight[$row.'-'.$this->page] = $this->GetY();
				}
				if($this->page > $maxpage)
					$maxpage = $this->page;
			}

			// get the height we were in the last used page
			$h = $tmpheight[$row.'-'.$maxpage];
			// set the "pointer" to the left margin
			$l = $this->lMargin;
			// set the $currpage to the last page
			$currpage = $maxpage;

			$numalineacion++;
		}
		
		$this->SetY($h+1);
		$this->page = $maxpage;
	}
}

// Creación del objeto de la clase heredada
$pdf = new PDF('P','mm','Letter');
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(182,182,182);

// $pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatouniformes.jpg",0,0,220,280);
$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/membreteuniformes.jpg",0,0,220,280);

$pdf->SetY(45);
$pdf->Cell(0,0,utf8_decode(fecha_formateada(date("Y-m-d"))),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(32,4,utf8_decode("Solicitud de compra"),'B',0,'L');

$_POST["idproveedor"] = $_GET["slcProveedor"];

$proveedor = ($_GET["slcProveedor"]>0) ? $claseProveedores->obtenerProveedor($_POST)["proveedor"]["nombre"] : " - ";
$pdf->Cell(164,4,utf8_decode("Proveedor: ".$proveedor),0,0,'R');

$pdf->Ln(8);
$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(5);

// conseguir los distintos productos de la lista (si es un producto de la tabla, tiene id, si no, se guarda su nombre)
$productos = array();
$idsolicitudes = array();
foreach($_GET["chkSolicitudes"] as $idsolicitud){

	$_POST["idsolicitudcompra"] = $idsolicitud;
	$solicitud = $claseSolicitudes->obtenerSolicitud($_POST)["solicitud"];
    $producto = (($solicitud["idproducto"]>0) ? $solicitud["idproducto"] : $solicitud["producto"]);

	array_push($idsolicitudes,$idsolicitud);

	if (!in_array($producto,$productos)) {
		array_push($productos,$producto);
	}
}

foreach($productos as $producto){

	$where = "";
	if (is_numeric($producto)) {
		$where = " and s.idproducto = " . $producto;
		$_POST["idproducto"] = $producto;
		$nombre = $claseProductos->obtenerProducto($_POST)["producto"]["nombre"];
	}else {
		$where = " and s.producto = '".$producto."'";
		$nombre = $producto; 
	}

    $data = array();
    $alineacion = array();

    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','B',9);
	$pdf->Cell(50,5,utf8_decode($nombre),0,'L');
    $pdf->Ln(2);

    $pdf->SetFont('Helvetica','B',8);
    $pdf->Ln(5);
    $pdf->Cell(36,5,utf8_decode('COLOR/TALLA'),'B',0,'L');

    $arrayAlineaciones = array();
    $arrayAlineaciones[] = 'L';
    $arrayTamanos = array();
    $arrayTamanos[] = 36;

    $_POST["idsolicitudes"] = $idsolicitudes;
    $_POST["where"] = $where;
	$tallas = $claseSolicitudes->obtenerTallasSolicitudes($_POST)["tallas"];
	// $tallas = mysqli_query($con,"select s.* from tsolicitudescompra s left join tcattallas t on t.idtalla=s.idtalla where s.idsolicitudcompra in (".implode(",",$idsolicitudes).") " . $where . " group by s.idtalla,s.talla order by t.posicion,s.talla");
    $tamano = floor(140/mysqli_num_rows($tallas));
	foreach($tallas as $talla){
		$pdf->Cell($tamano,5,utf8_decode($talla["talla"]),'B',0,'C');
        $arrayTamanos[] = $tamano;
        $arrayAlineaciones[] = 'C';

	}
	$pdf->Ln(5);

    $pdf->tablewidths = $arrayTamanos;

    $pdf->SetFont('Helvetica','',7);

    $colores = $claseSolicitudes->obtenerColoresSolicitudes($_POST)["colores"];
    // $colores = mysqli_query($con,"select s.* from tsolicitudescompra s left join tcatcolores c on c.idcolor=s.idcolor where s.idsolicitudcompra in (".implode(",",$idsolicitudes).") " . $where . " group by s.idcolor,s.color order by c.nombre,s.color");

	foreach($colores as $color){
        $arrayCantidades = array();
        $arrayCantidades[] = utf8_decode($color["color"]);
		// mysqli_data_seek($tallas,0);
        $_POST["idcolor"] = $color["idcolor"];
        $_POST["color"] = $color["color"];
		foreach($tallas as $talla){
            $_POST["idtalla"] = $talla["idtalla"];
            $_POST["talla"] = $talla["talla"];
			$cantidad = $claseSolicitudes->obtenerCantidadSolicitada($_POST)["total"];
			$cantidad = (($cantidad=="") ? 0 : $cantidad);
			// $cantidad = mysqli_fetch_assoc(mysqli_query($con,"select sum(cantidad) as cantidad from tsolicitudescompra s where s.idsolicitudcompra in (".implode(",",$idsolicitudes).") " . $where . " and s.idtalla='".$talla["idtalla"]."' and s.talla='".$talla["talla"]."' and s.idcolor='".$color["idcolor"]."' and s.color='".$color["color"]."'"))["cantidad"];
            $arrayCantidades[] = $cantidad;
		}
        $data[] = $arrayCantidades;
        $alineacion[] = $arrayAlineaciones;
	}
    $pdf->morepagestable($data,$alineacion);

}


$nombre = "SolicitudCompra".$_SESSION["usuario"]["idusuario"].date("YmdHis");

$pdf->Output('I',$nombre.".pdf");
?>
