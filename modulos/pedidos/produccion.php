<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Pedidos.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Movimientos.php");

$clasePedidos = new Pedidos();
$claseMovimientos = new Movimientos();

require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf/myfpdf.php");

$_POST["idpedido"] = $_GET["idpedido"];

$pedido = $clasePedidos->obtenerPedido($_POST)["pedido"];

// Creación del objeto de la clase heredada
$pdf = new MyFPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240);

$pdf->Image($_SERVER["DOCUMENT_ROOT"] . "/assets/images/membreteuniformes.jpg", 0, 0, 220, 280);

$pdf->SetY(45);
$pdf->Cell(0, 0, mb_convert_encoding(fecha_formateada_largo(explode(" ", $pedido["fecha"])[0]), "latin1", "UTF-8"), 0, 0, 'R');

$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(10);
$pdf->Cell(20, 4, mb_convert_encoding("Pedido: " . $pedido["idpedido"], "latin1", "UTF-8"), 'B', 0, 'L');

$pdf->SetFont('Helvetica', '', 9);
$pdf->Ln(10);
$pdf->Cell(0, 0, mb_convert_encoding('Attn: --- ' . (($pedido["cliente"] != "") ?  (($pedido["contacto"] != "") ? $pedido["contacto"] . " / " : "") . $pedido["cliente"] : "Público en general") . ' ---', "latin1", "UTF-8"), 0, 0, 'L');

$pdf->Ln(8);

// si el pedido tiene un total de 0, es pedido interno
if ($pedido["total"] > 0) {
    $_POST["pendiente"] = 1;
}
$_POST["status"] = 0;
$especificaciones = $clasePedidos->obtenerEspecificacionesPedido($_POST);

if ($especificaciones["respuesta"] == "OK") {
    foreach ($especificaciones["especificaciones"] as $especificacion) {
        $pdf->Ln(5);

        $posinicial = $pdf->GetY();

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(98, 0, mb_convert_encoding('Fecha de Entrega: ' . fecha_formateada_largo($especificacion["fechaentrega"]), "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->Cell(98, 0, mb_convert_encoding('Usuario: ' . $especificacion["usuario"], "latin1", "UTF-8"), 0, 0, 'R');

        $pdf->Ln(3);

        $posy = $pdf->GetY();

        $pdf->SetFont('Helvetica', '', 9);

        $texto = $especificacion["nombrediseno"] .
            ($especificacion["serigrafia"] == 1 ? "\n - Serigrafia" : "") .
            ($especificacion["digital"] == 1 ? "\n - Digital" : "") .
            ($especificacion["bordado"] == 1 ? "\n - Bordado" : "") .
            ($especificacion["especificaciones"] != "" ? "\n - Especificaciones: " . $especificacion["especificaciones"] : "");

        $textopartidas = $especificacion["desgloses"] . "\n" . $especificacion["partidas"];

        $pdf->SetWidths(array(98, 98));
        $pdf->SetAligns(array('L', 'L'));
        $pdf->Row(array(mb_convert_encoding($texto, "latin1", "UTF-8"), mb_convert_encoding($textopartidas, "latin1", "UTF-8")));

        $pdf->Ln(5);
    }
}

$productos = $clasePedidos->obtenerProductosAsignados($_POST);
if ($productos["respuesta"] == "OK") {
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(0, 6, "Productos asignados", 'TB', 0, 'C');

    $pdf->Ln(10);
    $pdf->Cell(16, 4, "Cantidad", 0, 0, 'L');
    $pdf->Cell(65, 4, "Producto", 0, 0, 'L');
    $pdf->Cell(45, 4, "Usuario", 0, 0, 'L');
    $pdf->Cell(40, 4, "Almacen", 0, 0, 'L');
    $pdf->Cell(30, 4, "Fecha", 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 9);

    foreach ($productos["productos"] as $producto) {
        $pdf->Ln(5);
        $pdf->Cell(16, 4, $producto["cantidad"], 0, 0, 'L');
        $pdf->CellFitScale(65, 4, mb_convert_encoding($producto["producto"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->CellFitScale(45, 4, mb_convert_encoding($producto["usuario"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->CellFitScale(40, 4, mb_convert_encoding($producto["almacen"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->CellFitScale(30, 4, $producto["fecha"], 0, 0, 'L');
    }

    $pdf->Ln(5);
}

$solicitudes = $clasePedidos->obtenerSolicitudes($_POST);
if ($solicitudes["respuesta"] == "OK") {
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(0, 6, "Compras", 'TB', 0, 'C');

    $pdf->Ln(10);
    $pdf->Cell(20, 4, "# Compra", 0, 0, 'L');
    $pdf->Cell(70, 4, "Producto", 0, 0, 'L');
    $pdf->Cell(30, 4, "Talla", 0, 0, 'L');
    $pdf->Cell(30, 4, "Color", 0, 0, 'L');
    $pdf->Cell(16, 4, "Cantidad", 0, 0, 'L');
    $pdf->Cell(30, 4, "Status", 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 9);

    foreach ($solicitudes["solicitudes"] as $partida) {
        $pdf->Ln(5);
        $pdf->Cell(20, 4, $partida["idcompra"] == 0 ? "-" : $partida["idcompra"], 0, 0, 'L');
        $pdf->CellFitScale(70, 4, mb_convert_encoding($partida["producto"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->Cell(30, 4, $partida["talla"], 0, 0, 'L');
        $pdf->Cell(30, 4, $partida["color"], 0, 0, 'L');
        $pdf->Cell(16, 4, $partida["cantidad"], 0, 0, 'L');
        $pdf->CellFitScale(30, 4, $partida["idcompraproducto"] > 0 ? $partida["cantidad_recibida"] == 0 ? "Sin recibir" : "Recibido" : "Sin comprar", 0, 0, 'L');
    }

    $pdf->Ln(5);
}

$movimientos = $clasePedidos->obtenerMovimientos($_POST);
if ($movimientos["respuesta"] == "OK") {
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(0, 6, "Movimientos", 'TB', 0, 'C');

    $pdf->Ln(10);
    $pdf->Cell(25, 4, "# Movimiento", 0, 0, 'L');
    $pdf->Cell(60, 4, "Producto", 0, 0, 'L');
    $pdf->Cell(20, 4, "Talla", 0, 0, 'L');
    $pdf->Cell(20, 4, "Color", 0, 0, 'L');
    $pdf->Cell(16, 4, "Cantidad", 0, 0, 'L');
    $pdf->Cell(30, 4, "Status", 0, 0, 'L');
    $pdf->Cell(25, 4, "Cantidad recibida", 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 9);

    foreach ($movimientos["movimientos"] as $partida) {
        $pdf->Ln(5);
        $pdf->Cell(25, 4, $partida["idmovimientoinventario"] == 0 ? "-" : $partida["idmovimientoinventario"], 0, 0, 'L');
        $pdf->CellFitScale(60, 4, mb_convert_encoding($partida["producto"], "latin1", "UTF-8"), 0, 0, 'L');
        $pdf->Cell(20, 4, $partida["talla"], 0, 0, 'L');
        $pdf->Cell(20, 4, $partida["color"], 0, 0, 'L');
        $pdf->Cell(16, 4, $partida["cantidad"], 0, 0, 'L');

        $_POST["idmovimientoinventario"] = $partida["idmovimientoinventario"];
        $movimiento = $claseMovimientos->obtenerMovimiento($_POST)["movimiento"];
        if ($movimiento["autorizacion"] == 0) {
            $status = "Sin Autorizar";
        } else if ($movimiento["autorizacion"] == 2) {
            if ($movimiento["recepcionparcial"] == 0) {
                $status = "Autorizado";
            } else if ($movimiento["recepcionparcial"] == 1) {
                $status = "Recibido Parcialmente";
            } else if ($movimiento["recepcionparcial"] == 2) {
                $status = "Recibido";
            }
        }else{
            $status = " ";
        }

        $pdf->CellFitScale(30, 4, $status, 0, 0, 'L');
        $pdf->Cell(25, 4, $partida["cantidadrecibida"] == 0 ? "-" : $partida["cantidadrecibida"], 0, 0, 'L');
    }
}

$pdf->Ln(15);
$pdf->SetFont('Helvetica', '', 10);

$nombre = "Produccion#" . $pedido["idpedido"];

$pdf->Output("I", $nombre . ".pdf");
