<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Sucursales.php");

$clasePedidos = new Pedidos();
$claseSucursales = new Sucursales();

$pedido = $clasePedidos->obtenerPedido(array("idpedido" => $_GET["idpedido"]))["pedido"];

require($_SERVER["DOCUMENT_ROOT"] . '/assets/plugins/fpdf/myfpdf.php');

// Creación del objeto de la clase heredada
$pdf = new MyFPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);

$formato = $claseSucursales->obtenerSucursal(array("idsucursal" => $pedido["idsucursal"]))["sucursal"]["formato"];
if(!is_null($formato) && file_exists($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato)){
	$pdf->Image($_SERVER["DOCUMENT_ROOT"]."/assets/images/formatos/".$formato, 0, 0, 220, 280);
}else{
	$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/membreteuniformes.jpg", 0, 0, 220, 280);
}

$pdf->SetY(45);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Ln(10);

$pdf->Ln(8);

$where = " and statusproduccion<2 ";
if (isset($_GET["idpedido"]) && $_GET["idpedido"] != -1) {
    $where .= " and idpedido = '" . $_GET["idpedido"] . "'";
}

$especificaciones = $clasePedidos->obtenerEspecificacionesPedido(array(
    "idpedido" => $_GET["idpedido"],
    "filtro" => $where
));


if ($especificaciones["respuesta"] == "OK") {
    foreach ($especificaciones["especificaciones"] as $especificacion) {

        $pdf->Ln(6);

        $posinicial = $pdf->GetY();

        $pdf->SetFont('Helvetica', 'B', 10);

        $pdf->Cell(98, 0, mb_convert_encoding('Pedido: ' . $especificacion["idpedido"] . " - " . $pedido["cliente"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->Ln(4);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(98, 0, mb_convert_encoding('Fecha de Entrega: ' . fecha_formateada($especificacion["fechaentrega"]), "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->Cell(98, 0, mb_convert_encoding('Usuario: ' . $especificacion["usuario"], "latin1", "UTF-8"), 0, 0, 'R');

        $pdf->Ln(3);

        $posy = $pdf->GetY();

        $pdf->SetFont('Helvetica', '', 9);

        $texto = $especificacion["nombrediseno"] .
            ($especificacion["serigrafia"] == 1 ? "\n - Serigrafia" : "") .
            ($especificacion["digital"] == 1 ? "\n - Digital" : "") .
            ($especificacion["bordado"] == 1 ? "\n - Bordado" : "") .
            ($especificacion["especificaciones"] != "" ? "\n - Especificaciones: " . $especificacion["especificaciones"] : "");

        $textopartidas = $especificacion["desgloses"];

        // si no tiene desgloses, poner los datos de la partida
        if ($textopartidas == "") {
            $textopartidas = $especificacion["partidas"];
        }

        $pdf->SetWidths(array(98, 98));
        $pdf->SetAligns(array('L', 'L'));
        $pdf->Row(array(mb_convert_encoding($texto, "latin1", "UTF-8"), mb_convert_encoding($textopartidas, "latin1", "UTF-8")));

        // $pdf->MultiCell(98,5,mb_convert_encoding($texto),0,'L');
        // $posy2 = $pdf->GetY();
        // $pdf->SetXY(108,($posy2<$posy ? $posy2 : $posy));

        // $pdf->MultiCell(98,5,mb_convert_encoding($texto),0,'L');
        $pdf->Ln(3);
        // $posy = $pdf->GetY();
        // $pdf->SetY($posy2>$posy ? $posy2 : $posy);


        // status almacen
        if ($especificacion["statusalmacen"] == 0) {
            $textoalmacen = "Faltan por surtir";
        } else if ($especificacion["statusalmacen"] == 1) {
            $textoalmacen = "Productos completamente surtidos";
        }
        // // status diseño
        // if ($especificacion["statusdiseno"]==0) {
        //     $textodiseno = "Sin acciones";
        // }else if ($especificacion["statusdiseno"]==1) {
        //     $textodiseno = "Iniciado";
        // }else if ($especificacion["statusdiseno"]==2) {
        //     $textodiseno = "En espera de aprobación";
        // }else if ($especificacion["statusdiseno"]==4) {
        //     $textodiseno = "Aprobado";
        // }else if ($especificacion["statusdiseno"]==5) {
        //     $textodiseno = "Terminado";
        // }
        // status produccion
        if ($especificacion["statusproduccion"] == 0) {
            $textoproduccion = "Sin acciones";
        } else if ($especificacion["statusproduccion"] == 1) {
            $textoproduccion = "Producción iniciada";
        } else if ($especificacion["statusproduccion"] == 2) {
            $textoproduccion = "Producción terminada";
        }

        $posy = $pdf->GetY();
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetWidths(array(100, 100));
        $pdf->SetAligns(array('C', 'C'));
        $pdf->Row(array(mb_convert_encoding("Almacén", "latin1", "UTF-8"), mb_convert_encoding("Producción", "latin1", "UTF-8")));
        // $pdf->MultiCell(100,5,mb_convert_encoding("Almacén"),0,'C');
        // $posy2 = $pdf->getY() - 5;
        // // $pdf->SetY($posy2<$posy ? $posy2 : $posy);
        // // $pdf->MultiCell(200,5,mb_convert_encoding("Diseño"),0,'C');
        // // $posy2 = $pdf->getY() - 5;
        // $pdf->SetY($posy2<$posy ? $posy2 : $posy);
        // $pdf->MultiCell(300,5,mb_convert_encoding("Producción"),0,'C');

        $pdf->Ln(3);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Row(array(mb_convert_encoding($textoalmacen, "latin1", "UTF-8"), mb_convert_encoding($textoproduccion, "latin1", "UTF-8")));
        // $posy = $pdf->GetY();
        // $pdf->MultiCell(100,5,mb_convert_encoding($textoalmacen),0,'C');
        // $posy2 = $pdf->getY() - 5;
        // // $pdf->SetY($posy2<$posy ? $posy2 : $posy);
        // // $pdf->MultiCell(200,5,mb_convert_encoding($textodiseno),0,'C');
        // // $posy2 = $pdf->getY() - 5;
        // $pdf->SetY($posy2<$posy ? $posy2 : $posy);
        // $pdf->MultiCell(300,5,mb_convert_encoding($textoproduccion),0,'C');

    }
}

$pdf->Ln(15);
$pdf->SetFont('Helvetica', '', 10);

$nombre = "Lista Especificaciones Pendientes " . $cliente["nombre"];

$nombre = str_replace(" ", "_", $nombre);

$pdf->Output("I", $nombre . ".pdf");
