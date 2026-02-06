<?
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Productos.php");

$claseProductos = new Productos();

require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/fpdf/fpdf.php");

class PDF extends FPDF
{

    public $tablewidths;
    public $footerset;

    function _beginpage($orientation, $size, $rotation)
    {
        $this->page++;
        if (!isset($this->pages[$this->page])) // solves the problem of overwriting a page if it already exists
            $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        // Check page size and orientation
        if ($orientation == '')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if ($size == '')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if ($orientation != $this->CurOrientation || $size[0] != $this->CurPageSize[0] || $size[1] != $this->CurPageSize[1]) {
            // New size or orientation
            if ($orientation == 'P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w * $this->k;
            $this->hPt = $this->h * $this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if ($orientation != $this->DefOrientation || $size[0] != $this->DefPageSize[0] || $size[1] != $this->DefPageSize[1])
            $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
        if ($rotation != 0) {
            if ($rotation % 90 != 0)
                $this->Error('Incorrect rotation value: ' . $rotation);
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    // Pie de página
    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print centered page number
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'C');
    }

    function morepagestable($datas, $alineaciones, $lineheight = 3)
    {
        // some things to set and 'remember'
        $l = $this->lMargin;
        $startheight = $h = $this->GetY();
        $startpage = $currpage = $maxpage = $this->page;

        // calculate the whole width
        $fullwidth = 0;
        foreach ($this->tablewidths as $width) {
            $fullwidth += $width;
        }

        // Now let's start to write the table
        $numalineacion = 0;
        foreach ($datas as $row => $data) {
            $this->page = $currpage;
            // write the horizontal borders
            $this->Line($l, $h, $fullwidth + $l, $h);
            // write the content and remember the height of the highest col
            foreach ($data as $col => $txt) {
                $this->page = $currpage;
                $this->SetXY($l, $h + 1);
                $this->MultiCell($this->tablewidths[$col], $lineheight, $txt, 0, $alineaciones[$numalineacion][$col]);
                $l += $this->tablewidths[$col];

                if (!isset($tmpheight[$row . '-' . $this->page]))
                    $tmpheight[$row . '-' . $this->page] = 0;
                if ($tmpheight[$row . '-' . $this->page] < $this->GetY()) {
                    $tmpheight[$row . '-' . $this->page] = $this->GetY();
                }
                if ($this->page > $maxpage)
                    $maxpage = $this->page;
            }

            // get the height we were in the last used page
            $h = $tmpheight[$row . '-' . $maxpage];
            // set the "pointer" to the left margin
            $l = $this->lMargin;
            // set the $currpage to the last page
            $currpage = $maxpage;

            $numalineacion++;
        }

        $this->SetY($h + 1);
        $this->page = $maxpage;
    }
}

$_POST = $_GET;
file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/txts/pruebaaa.txt", print_r($_GET, true));

$_POST["idtalla"] = $_GET["slcTalla"];
$_POST["idcolor"] = $_GET["slcColor"];

// Creación del objeto de la clase heredada
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(20, 20);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetFillColor(182, 182, 182);

$pdf->Cell(176, 0, utf8_decode('Reporte de Existencias Desglosado por Almacenes'), 0, 0, 'L');
$pdf->Ln(5);

//AQUI INICIA EL CICLO DE PRODUCTOS
$productos = $claseProductos->obtenerProductos($_POST);
foreach ($productos["productos"] as $producto) {

    $_POST["idproducto"] = $producto["idproducto"];
    $_POST["slcAlmacenes"] = $_GET["slcAlmacenes"];
    $almacenes = $claseProductos->obtenerAlmacenesProducto($_POST);
    foreach ($almacenes["almacenes"] as $almacen) {

        // unset($_POST["idcolor"]);
        // unset($_POST["idtalla"]);
        $data = array();
        $alineacion = array();

        $pdf->Ln(5);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(176, 5, utf8_decode($producto["nombre"] . "-" . $almacen["almacen"]), '', 0, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Ln(5);
        $pdf->Cell(36, 5, utf8_decode('COLOR/TALLA'), 'B', 0, 'L');

        $arrayAlineaciones = array();
        $arrayAlineaciones[] = 'L';
        $arrayTamanos = array();
        $arrayTamanos[] = 36;

        unset($_POST);
        $_POST["idalmacen"] = $almacen["idalmacen"];
        $_POST["idproducto"] = $producto["idproducto"];
        if ($claseProductos->manejaTallasColores($_POST)) {
            $_POST["ordenar"] = 1;
            $tallas = $claseProductos->obtenerTallasProducto($_POST);
            $tamano = floor(140 / mysqli_num_rows($tallas["tallas"]));
            foreach ($tallas["tallas"] as $talla) {
                $pdf->Cell($tamano, 5, utf8_decode($talla["talla"]), 'B', 0, 'C');
                $arrayTamanos[] = $tamano;
                $arrayAlineaciones[] = 'C';
            }
            $pdf->Ln(5);

            $pdf->tablewidths = $arrayTamanos;

            $pdf->SetFont('Helvetica', '', 7);

            $colores = $claseProductos->obtenerColoresProducto($_POST);
            foreach ($colores["colores"] as $color) {
                $_POST["idcolor"] = $color["idcolor"];
                $arrayExistencias = array();
                $arrayExistencias[] = utf8_decode($color["color"]);
                foreach ($tallas["tallas"] as $talla) {
                    $_POST["idproducto"] = $producto["idproducto"];
                    $_POST["idtalla"] = $talla["idtalla"];
                    $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
                    $existencia = $datos["existencias"];

                    // cantidad reservada por movimientos
                    $cantidadreservada = $datos["reservado"];

                    if ($cantidadreservada > 0) {
                        $arrayExistencias[] = $existencia . "/" . $cantidadreservada;
                    } else {
                        $arrayExistencias[] = $existencia;
                    }
                }
                $data[] = $arrayExistencias;
                $alineacion[] = $arrayAlineaciones;
            }
        } else {
            $tamano = 140;
            // foreach($tallas["tallas"] as $talla){
            $pdf->Cell($tamano, 5, utf8_decode("N/A"), 'B', 0, 'C');
            $arrayTamanos[] = $tamano;
            $arrayAlineaciones[] = 'C';
            // }
            $pdf->Ln(5);

            $pdf->tablewidths = $arrayTamanos;

            $pdf->SetFont('Helvetica', '', 7);

            $arrayExistencias = array();
            $arrayExistencias[] = utf8_decode("N/A");

            $_POST["sincolortalla"] = 1;
            $datos = $claseProductos->obtenerExistenciasYReservadoProducto($_POST);
            $existencia = $datos["existencias"];

            // cantidad reservada por movimientos
            $cantidadreservada = $datos["reservado"];

            if ($cantidadreservada > 0) {
                $arrayExistencias[] = $existencia . "/" . $cantidadreservada;
            } else {
                $arrayExistencias[] = $existencia;
            }
            $data[] = $arrayExistencias;
            $alineacion[] = $arrayAlineaciones;
        }

        $pdf->morepagestable($data, $alineacion);
    }
}

$pdf->Output();
