<?
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/sesion.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/seguridad.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/generales.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/otros/con.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/clases/Movimientos.php");

$claseMovimientos = new Movimientos();

require($_SERVER["DOCUMENT_ROOT"]."/assets/plugins/fpdf/fpdf.php");

$fecha = date("Y-m-d");

// Creación del objeto de la clase heredada
$pdf = new FPDF('P','mm','Letter');
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(240,240,240);
$pdf->SetAutoPageBreak(false);

$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/membreteuniformes.jpg",0,0,220,280);

$pdf->SetY(45);
$pdf->Cell(0,0,utf8_decode(fecha_formateada_largo($fecha)),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(20,4,utf8_decode("Packing List"),'B',0,'L');
    
$_POST["idsmovimientos"] = $_GET["idsmovimientos"];
if ($_GET["tipo"]=="agrupados") {
    $pdf->Ln(8);
    $pdf->SetFont('Helvetica','B',9);
    $pdf->Cell(195,5,utf8_decode('Productos'),'',0,'L');
    $pdf->Ln(5);

    $num = 1;
    $movimientos = $claseMovimientos->obtenerMovimientos3($_POST);
    foreach($movimientos["movimientos"] as $partida){
        $extras = "\n" . $partida["desgloses"];

        $pdf->Ln(1);
        $posy = $pdf->GetY();
        $pdf->SetFont('Helvetica','',9);
        $pdf->MultiCell(195,5,utf8_decode($partida["producto"].$extras),0,'L',($num%2==0) ? true : false);

        $posy2 = $pdf->GetY();
        $pdf->SetXY(195,$posy);

        if($posy2>$pdf->GetPageHeight()-10){
            $pdf->SetFillColor(255,255,255);
            $pdf->Rect(10,$posy,196,$pdf->GetPageHeight()-$posy,'F');
            $pdf->SetFillColor(240,240,240);
            $pdf->AddPage();
            $pdf->SetY(10);

            $posy = $pdf->GetY();
            $pdf->SetFont('Helvetica','',9);
            $pdf->MultiCell(195,5,utf8_decode($partida["producto"].$extras),0,'L',($num%2==0) ? true : false);
            
            $posy2 = $pdf->GetY();
            $pdf->SetXY(195,$posy);
            
            if($num%2==0 && $posy2>$posy+5){
                $pdf->Rect(195,$posy+5,90,$posy2-($posy+5),'F');
            }
        }

        $pdf->SetY($posy2);
        $num++;
    }
} else {
    // desglosados
    $movimientos = $claseMovimientos->obtenerMovimientos2($_POST);
    $j = 0;
    $totalmovimientos = $movimientos["totalmovimientos"];
    foreach ($movimientos["movimientos"] as $movimiento) {
        
        $pdf->Ln(8);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(0,0,utf8_decode('Movimiento #' . $movimiento["folio"]),0,0,'L');
    
        $pdf->Ln(8);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(195,5,utf8_decode('Productos'),'',0,'L');
        $pdf->Ln(5);
    
        $num = 1;
        $partidas = explode(":",$movimiento["productos"]);
        foreach ($partidas as $partida) {
            $producto = explode(",",explode(".-",$partida)[0])[1];
    
            $extras = "";

            $desgloses = explode(".-",$partida)[1];
            $desgloses = explode(";",$desgloses);
            foreach ($desgloses as $desglose) {
                $extras .= $desglose."\n";
            }
            $extras = rtrim($extras,"\n");
        
            $pdf->Ln(1);
        
            $posy = $pdf->GetY();
        
            $pdf->SetFont('Helvetica','',9);
        
            $pdf->MultiCell(195,5,utf8_decode($producto)."\n".$extras,0,'L',($num%2==0) ? true : false);
        
            $posy2 = $pdf->GetY();
        
            $pdf->SetXY(195,$posy);
        
            if($posy2>$pdf->GetPageHeight()-10){
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10,$posy,195,$pdf->GetPageHeight()-$posy,'F');
                $pdf->SetFillColor(240,240,240);
                $pdf->AddPage();
                $pdf->SetY(10);
        
                $posy = $pdf->GetY();
        
                $pdf->SetFont('Helvetica','',9);
        
                $pdf->MultiCell(195,5,utf8_decode($producto).$extras,0,'L',($num%2==0) ? true : false);
        
                $posy2 = $pdf->GetY();
        
                $pdf->SetXY(195,$posy);
                
                if($num%2==0 && $posy2>$posy+5){
                    $pdf->Rect(195,$posy+5,90,$posy2-($posy+5),'F');
                }
            }
            
            $pdf->SetY($posy2);
            $num++;
        }
    
        $j++;
        if ($j!=$totalmovimientos) {
            $pdf->AddPage();
        }
    }
}

$pdf->SetAutoPageBreak(true);

$pdf->Ln(15);
$pdf->SetFont('Helvetica','',10);

$nombre = "Packing List ".$fecha;

$nombre = str_replace(" ","_",$nombre);

$pdf->Output("I",$nombre.".pdf");

?>