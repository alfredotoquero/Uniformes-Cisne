<?
function limpiarString($string){
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    /*$string = str_replace(
        array("\", "¨", "º", "-", "~",
             "#", "@", "|", "!", """,
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        '',
        $string
    );*/
 
 
    return $string;
}

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
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Usuarios.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Sucursales.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Pedidos.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

$claseReportes = new Reportes();
$claseUsuarios = new Usuarios();
$claseSucursales = new Sucursales();
$clasePedidos = new Pedidos();

require($_SERVER["DOCUMENT_ROOT"]."/assets/plugins/fpdf/fpdf.php");

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

    // Pie de página
    function Footer(){
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial','I',8);
        // Print centered page number
        $this->Cell(0,10,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'C');
    }

    function morepagestable($datas, $alineaciones, $lineheight=3) {
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
            $this->Line($l,$h,$fullwidth+$l,$h);
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

    function CellFit($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $scale = false, $force = true)
    {
        //Get string width
        $str_width = $this->GetStringWidth($txt);

        //Calculate ratio to fit cell
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $ratio = ($w - $this->cMargin * 2) / $str_width;

        $fit = ($ratio < 1 || ($ratio > 1 && $force));
        if ($fit) {
            if ($scale) {
                //Calculate horizontal scaling
                $horiz_scale = $ratio * 100.0;
                //Set horizontal scaling
                $this->_out(sprintf('BT %.2F Tz ET', $horiz_scale));
            } else {
                //Calculate character spacing in points
                $char_space = ($w - $this->cMargin * 2 - $str_width) / max(strlen($txt) - 1, 1) * $this->k;
                //Set character spacing
                $this->_out(sprintf('BT %.2F Tc ET', $char_space));
            }
            //Override user alignment (since text will fill up cell)
            $align = '';
        }

        //Pass on to Cell method
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

        //Reset character spacing/horizontal scaling
        if ($fit)
            $this->_out('BT ' . ($scale ? '100 Tz' : '0 Tc') . ' ET');
    }

    //Cell with horizontal scaling only if necessary
    function CellFitScale($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $this->CellFit($w, $h, $txt, $border, $ln, $align, $fill, $link, true, false);
    }
}

$_POST["idcorte"] = $_GET["idcorte"];
$corte = $claseReportes->obtenerCorteGeneral($_POST)["corte"];

$_POST["buscarcorteanterior"] = 1;
$_POST["idsucursal"] = $corte["idsucursal"];
$corteanterior = $claseReportes->obtenerCorte($_POST)["corte"];

$sucursal = $claseSucursales->obtenerSucursal($_POST)["sucursal"];

// Creación del objeto de la clase heredada
$pdf = new PDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(182,182,182);

// $pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/formato".$usuario["formato"].".jpg",0,0,220,280);

$pdf->Cell(0,0,utf8_decode("De ".fecha_formateada(explode(" ",$corte["fechainicial"])[0])." a ".fecha_formateada(explode(" ",$corte["fechafinal"])[0])),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(98,4,utf8_decode($sucursal["nombre"]),0,0,'L');
$pdf->Cell(98,4,utf8_decode("Corte #".$corte["folio"]),0,0,'R');

$pdf->Ln(10);
$pdf->SetFont('Helvetica','B',8);
$pdf->Cell(88,4,utf8_decode("CLIENTE"),'B',0,'L');
$pdf->Cell(20,4,utf8_decode("MONTO"),'B',0,'L');
$pdf->Cell(48,4,utf8_decode("CONCEPTO"),'B',0,'L');
$pdf->Cell(40,4,utf8_decode("F. DE PAGO"),'B',0,'L');

//se obtienen los tickets de pedidos para su desglose
$_POST["tipo"] = 1;
$tickets = $claseReportes->obtenerTickets($_POST);
foreach($tickets["tickets"]  as $ticket){

    $_POST["idpedido"] = $ticket["idpedido"];
    $pedido = $clasePedidos->obtenerPedido($_POST)["pedido"];
    // $pedido = mysqli_fetch_assoc(mysqli_query($con,"select * from vpedidos where idpedido = '".$ticket["idpedido"]."'"));

    $status = "";
    if($pedido["statuspago"]==1 && mysqli_num_rows(mysqli_query($con,"select * from ttickets where idpedido = '".$ticket["idpedido"]."' and idticket > '".$ticket["idticket"]."'"))==0){
        $status = " (Liquidación - Ticket #".$ticket["folio"].")";
    }else if(mysqli_num_rows(mysqli_query($con,"select * from ttickets where idpedido = '".$ticket["idpedido"]."' and idticket < '".$ticket["idticket"]."'"))>0){
        $status = " (Abono - Ticket #".$ticket["folio"].")";
    }else if(mysqli_num_rows(mysqli_query($con,"select * from ttickets where idpedido = '".$ticket["idpedido"]."' and idticket < '".$ticket["idticket"]."'"))==0){
        $status = " (Anticipo - Ticket #".$ticket["folio"].")";
    }

    $arrayFormasPago = array();
    $formaspago = mysqli_query($con,"select * from tcatformaspago where idformapago in (select idformapago from tformaspagoticket where idticket = '".$ticket["idticket"]."')");
    while($formapago = mysqli_fetch_assoc($formaspago)){

        array_push($arrayFormasPago,$formapago["nombre"]);
    }
    $formaspago = implode(", ",$arrayFormasPago);

    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','',7);
    $pdf->Cell(88,4,utf8_decode($pedido["cliente"]),'B',0,'L');
    $pdf->Cell(20,4,"$".number_format($ticket["total"],2),'B',0,'L');
    $pdf->Cell(48,4,utf8_decode("Pedido #".$pedido["idpedido"].$status),'B',0,'L');
    $pdf->CellFitScale(40,4,utf8_decode($formaspago),'B',0,'L');

}

//se obtienen los tickets de pedidos para su desglose
$_POST["tipo"] = 0;
$tickets = $claseReportes->obtenerTickets($_POST);
foreach($tickets["tickets"] as $ticket){

    $arrayFormasPago = array();
    $formaspago = mysqli_query($con,"select * from tcatformaspago where idformapago IN (select idformapago from tformaspagoticket where idticket = '".$ticket["idticket"]."') OR '0'");

    while($formapago = mysqli_fetch_assoc($formaspago)){
        array_push($arrayFormasPago,isset($formapago["nombre"]) && !empty($formapago["nombre"]) ? $formapago["nombre"] : "N/A");
    }
    $formaspago = implode(", ",$arrayFormasPago);
    
    // file_put_contents($_SERVER["DOCUMENT_ROOT"]."/pruebaPdf.txt", print_r("test2",true));
    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','',7);
    $pdf->Cell(88,4,utf8_decode(""),'B',0,'L');
    $pdf->Cell(20,4,"$".number_format($ticket["total"],2),'B',0,'L');
    $pdf->Cell(48,4,utf8_decode("Ticket #".$ticket["folio"]),'B',0,'L');
    $pdf->CellFitScale(40,4,utf8_decode(isset($formaspago) && !empty($formaspago) ? $formaspago : "N/A"),'B',0,'L');
}

// $totaltickets = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from ttickets where idcorte = '".$corte["idcorte"]."' and idpedido = 0"))["total"];

// $pdf->Ln(5);
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(88,4,utf8_decode(""),'B',0,'L');
// $pdf->Cell(20,4,"$".number_format($totaltickets,2),'B',0,'L');
// $pdf->Cell(48,4,utf8_decode("TICKETS"),'B',0,'L');
// $pdf->Cell(40,4,utf8_decode(""),'B',0,'L');

$totalventas = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from ttickets where idcorte = '".$corte["idcorte"]."'"))["total"];

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',7);
$pdf->Cell(88,4,utf8_decode("TOTAL DE VENTAS"),'B',0,'L');
$pdf->Cell(20,4,"$".number_format($totalventas,2),'B',0,'L');
$pdf->Cell(48,4,utf8_decode(""),'B',0,'L');
$pdf->Cell(40,4,utf8_decode(""),'B',0,'L');
$pdf->Ln(5);

$totalventas = 0;
// $formaspago = array();
$result = mysqli_query($con, "select a.*, b.nombre from tcortesucursal_formaspago_arqueo a left join tcatformaspago b on b.idformapago = a.idformapago where a.idcorte = '" . $corte["idcorte"] . "'");

while ($formapago = mysqli_fetch_assoc($result)) {
    $monto = $formapago["total"];
    $nombre = $formapago["nombre"];
    
    $pdf->Ln(5);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetX(156);
    $pdf->Cell(25, 4, utf8_decode(strtoupper($nombre)), 'B', 0, 'R');
    $pdf->SetFont('Helvetica', '', 7);
    if ($formapago['idformapago'] == 2) { //dolar
        $pdf->Cell(25, 4, "$" . number_format($monto * $corte['tipocambio'], 2) . " ($" . number_format($monto, 2) . ")", 'B', 0, 'R');
        $totalventas += ($monto * $corte["tipocambio"]);
    } else {
        $totalventas += $monto;
        $pdf->Cell(25, 4, "$" . number_format($monto, 2), 'B', 0, 'R');
    }
}

// $pdf->Ln(10);
// $pdf->SetFont('Helvetica','B',8);
// $pdf->SetX(156);
// $pdf->Cell(25,4,utf8_decode("T/C"),'B',0,'R');
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(25,4,"$".number_format($corte["arqueo_tarjeta"],2),'B',0,'R');

// $pdf->Ln(5);
// $pdf->SetFont('Helvetica','B',8);
// $pdf->SetX(156);
// $pdf->Cell(25,4,utf8_decode("DLLS"),'B',0,'R');
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(25,4,"$".number_format($corte["arqueo_efectivousd"]*$corte["tipocambio"],2)." ($".number_format($corte["arqueo_efectivousd"],2).")",'B',0,'R');

// $pdf->Ln(5);
// $pdf->SetFont('Helvetica','B',8);
// $pdf->SetX(156);
// $pdf->Cell(25,4,utf8_decode("M.N."),'B',0,'R');
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(25,4,"$".number_format($corte["arqueo_efectivo"],2),'B',0,'R');

// $pdf->Ln(5);
// $pdf->SetFont('Helvetica','B',8);
// $pdf->SetX(156);
// $pdf->Cell(25,4,utf8_decode("CHEQUE"),'B',0,'R');
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(25,4,"$".number_format($corte["arqueo_cheque"],2),'B',0,'R');

// $pdf->Ln(5);
// $pdf->SetFont('Helvetica','B',8);
// $pdf->SetX(156);
// $pdf->Cell(25,4,utf8_decode("TRANSFERENCIA"),'B',0,'R');
// $pdf->SetFont('Helvetica','',7);
// $pdf->Cell(25,4,"$".number_format($corte["arqueo_transferencia"],2),'B',0,'R');

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("FERIA"),'B',0,'R');
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(25,4,"$".number_format($corte["feria"],2),'B',0,'R');

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("GASTO"),'B',0,'R');
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(25,4,"$".number_format($corte["gastos"],2),'B',0,'R');

// $totalventas = $corte["arqueo_efectivo"] + ($corte["arqueo_efectivousd"]*$corte["tipocambio"]) + $corte["arqueo_tarjeta"] + $corte["arqueo_transferencia"] + $corte["arqueo_cheque"] + $corte["feria"] + $corte["gastos"];
$totalventas += $corte["feria"] + $corte["gastos"];
$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("TOTAL"),'B',0,'R');
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(25,4,"$".number_format($totalventas,2),'B',0,'R');

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("FERIA AYER"),'B',0,'R');
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(25,4,"$".number_format($corteanterior["feria"],2),'B',0,'R');

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("TOTAL VENTA"),'B',0,'R');
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(25,4,"$".number_format($totalventas-$corteanterior["feria"],2),'B',0,'R');

$diferencia = ($totalventas-$corteanterior["feria"]) - $corte["ventas"];

$pdf->Ln(5);
$pdf->SetFont('Helvetica','B',8);
$pdf->SetX(156);
$pdf->Cell(25,4,utf8_decode("DIFERENCIA"),'B',0,'R');
if($diferencia>0){
    $pdf->SetFont('Helvetica','B',7);
}else if($diferencia<0){
    $pdf->SetTextColor(255,0,0);
    $pdf->SetFont('Helvetica','B',7);
}else{
    $pdf->SetFont('Helvetica','',7);
}
$pdf->Cell(25,4,"$".(($diferencia>0) ? "+" : "").number_format($diferencia,2),'B',0,'R');

//se obtienen los gastos del corte
$gastos = mysqli_query($con,"select * from tgastos where idcorte = '".$corte["idcorte"]."'");
$devoluciones = mysqli_query($con,"select * from vdevoluciones where idcorte='".$corte["idcorte"]."'");

if(mysqli_num_rows($gastos)>0 or mysqli_num_rows($devoluciones)>0){

    $pdf->SetTextColor(0,0,0);
    $pdf->Ln(10);
    $pdf->SetFont('Helvetica','B',8);
    $pdf->Cell(176,4,utf8_decode("CONCEPTO DE GASTO"),'B',0,'L');
    $pdf->Cell(20,4,utf8_decode("MONTO"),'B',0,'L');
}

while($gasto = mysqli_fetch_assoc($gastos)){
    
    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','',7);
    $pdf->Cell(176,4,utf8_decode($gasto["motivo"]),'B',0,'L');
    $pdf->Cell(20,4,"$".number_format($gasto["monto"],2),'B',0,'L');

}
    
// se obtienen las devoluciones del corte 
$devoluciones = mysqli_query($con,"select * from vdevoluciones where idcorte='".$corte["idcorte"]."'");
while($devolucion = mysqli_fetch_assoc($devoluciones)){
    // codigo
    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','',7);
    $pdf->Cell(176,4,utf8_decode("Devolución Ticket #" . $devolucion["folio"]),'B',0,'L');
    $total = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from trcuentaproductos where idcuenta='".$devolucion["idcuenta"]."' and idcuentaproducto in (select idcuentaproducto from vdevoluciones)"))["total"];
    $pdf->Cell(20,4,"$".number_format($total,2),'B',0,'L');
}


// $pdf->SetTextColor(0,0,0);


$pdf->Output("I","Corte#".$corte["folio"].".pdf");
?>