<?
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 600);
header("Set-Cookie: fileDownload=true; path=/");

include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/sesion.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/seguridad.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/otros/generales.php");


include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Compras.php");
include($_SERVER["DOCUMENT_ROOT"] . "/assets/php/clases/Proveedores.php");

$claseCompras = new Compras();

//se incluyen los archivos de excel
require($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;


$objPHPExcel = new Spreadsheet();
// include($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/PHPExcel/PHPExcel.php");
// include($_SERVER["DOCUMENT_ROOT"] . "/assets/plugins/PHPExcel/PHPExcel/Writer/Excel2007.php");

// $objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setTitle("Stands");

$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Detalle de la Compra');

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(array('alignment' => array('horizontal' => 'center'), 'font' => array('bold' => true, 'color' => array('rgb' => '000000'))));

$objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Cantidad');
$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(array('alignment' => array('horizontal' => 'center'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$objPHPExcel->getActiveSheet()->SetCellValue('B3', 'Producto');
$objPHPExcel->getActiveSheet()->getStyle('B3')->applyFromArray(array('alignment' => array('horizontal' => 'center'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$objPHPExcel->getActiveSheet()->SetCellValue('C3', 'Talla');
$objPHPExcel->getActiveSheet()->getStyle('C3')->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$objPHPExcel->getActiveSheet()->SetCellValue('D3', 'Color');
$objPHPExcel->getActiveSheet()->getStyle('D3')->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$objPHPExcel->getActiveSheet()->SetCellValue('E3', 'Pedido');
$objPHPExcel->getActiveSheet()->getStyle('E3')->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$objPHPExcel->getActiveSheet()->SetCellValue('F3', 'Usuario');
$objPHPExcel->getActiveSheet()->getStyle('F3')->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')), 'fill' => array('type' => 'solid', 'color' => array('rgb' => '214A7B'))));

$num = 4;
$productos = $claseCompras->obtenerProductosCompra($_POST);
foreach ($productos["productos"] as $producto) {
    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $producto["cantidad"]);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'center'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $producto["producto"]);
    $objPHPExcel->getActiveSheet()->getStyle('B' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $producto["talla"]);
    $objPHPExcel->getActiveSheet()->getStyle('C' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, $producto["color"]);
    $objPHPExcel->getActiveSheet()->getStyle('D' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, (($producto["idpedido"] > 0) ? $producto["idpedido"] : "-"));
    $objPHPExcel->getActiveSheet()->getStyle('E' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $producto["usuario"]);
    $objPHPExcel->getActiveSheet()->getStyle('F' . $num)->applyFromArray(array('alignment' => array('horizontal' => 'left'), 'borders' => array('allborders' => array('style' => 'dashed', 'color' => array('rgb' => '000000')))));

    $objPHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(-1);

    $num++;
}

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

$objPHPExcel->setActiveSheetIndex(0);

$nombre = "ProductosCompra" . $_SESSION["usuario"]["idusuario"] . date("YmdHis");

// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter = IOFactory::createWriter($objPHPExcel, 'Xls');
$objWriter->save($_SERVER["DOCUMENT_ROOT"] . "/archivos/compras/" . $nombre . '.xls');

echo $nombre;
